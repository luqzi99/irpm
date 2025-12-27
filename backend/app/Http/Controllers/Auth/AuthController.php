<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    // Allowed email domains
    private const ALLOWED_DOMAINS = ['moe.gov.my'];
    private const BYPASS_DOMAINS = ['test.irpm.my']; // Auto-verified for testing

    /**
     * Login user and return token
     */
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Emel atau kata laluan tidak sah.'],
            ]);
        }

        // Check if account is active
        if (!$user->is_active) {
            return response()->json([
                'message' => 'Akaun anda telah dinyahaktifkan. Sila hubungi pentadbir.',
                'account_disabled' => true,
            ], 403);
        }

        // Check subscription status (skip for admin and unverified users)
        if (!$user->isAdmin() && $user->email_verified_at && !$user->isSubscriptionActive()) {
            return response()->json([
                'message' => 'Langganan anda telah tamat. Sila hubungi pentadbir untuk memperbaharui.',
                'subscription_expired' => true,
                'expired_at' => $user->subscription_expires_at?->format('d/m/Y'),
            ], 403);
        }

        // Revoke old tokens
        $user->tokens()->delete();

        // Create new token
        $token = $user->createToken('auth-token')->plainTextToken;

        // Audit log
        AuditLog::log($user->id, AuditLog::ACTION_LOGIN, 'User', $user->id);

        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'email_verified_at' => $user->email_verified_at,
                'subscription_plan' => $user->subscription_plan,
                'subscription_label' => $user->getSubscriptionLabel(),
            ],
            'token' => $token,
        ]);
    }

    /**
     * Register new guru
     */
    public function register(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6|confirmed',
        ]);

        // Validate email domain
        $emailDomain = Str::after($request->email, '@');
        $isAllowedDomain = in_array($emailDomain, self::ALLOWED_DOMAINS);
        $isBypassDomain = in_array($emailDomain, self::BYPASS_DOMAINS);

        if (!$isAllowedDomain && !$isBypassDomain) {
            return response()->json([
                'message' => 'Hanya emel @moe.gov.my dibenarkan untuk pendaftaran.',
                'errors' => [
                    'email' => ['Sila gunakan emel rasmi KPM (@moe.gov.my).']
                ]
            ], 422);
        }

        // Generate verification token
        $verificationToken = Str::random(64);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'guru',
            'verification_token' => $isBypassDomain ? null : $verificationToken,
            'email_verified_at' => $isBypassDomain ? now() : null, // Auto-verify bypass domains
        ]);

        // Send verification email (skip for bypass domains)
        if (!$isBypassDomain) {
            $this->sendVerificationEmail($user, $verificationToken);
            
            return response()->json([
                'message' => 'Pendaftaran berjaya! Sila semak emel anda untuk pengesahan.',
                'requires_verification' => true,
            ], 201);
        }

        // For bypass domain, auto-login
        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
            ],
            'token' => $token,
            'message' => 'Pendaftaran berjaya! (Bypass mode)',
        ], 201);
    }

    /**
     * Verify email
     */
    public function verifyEmail(Request $request): JsonResponse
    {
        $request->validate([
            'token' => 'required|string',
        ]);

        $user = User::where('verification_token', $request->token)->first();

        if (!$user) {
            return response()->json([
                'message' => 'Token pengesahan tidak sah atau telah tamat tempoh.',
            ], 400);
        }

        $user->update([
            'email_verified_at' => now(),
            'verification_token' => null,
        ]);

        // Create token for auto-login after verification
        $token = $user->createToken('auth-token')->plainTextToken;

        AuditLog::log($user->id, 'email_verified', 'User', $user->id);

        return response()->json([
            'message' => 'Emel berjaya disahkan! Anda akan dialihkan ke papan pemuka.',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'email_verified_at' => $user->email_verified_at,
                'subscription_plan' => $user->subscription_plan,
                'subscription_label' => $user->getSubscriptionLabel(),
            ],
            'token' => $token,
        ]);
    }

    /**
     * Resend verification email
     */
    public function resendVerification(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json([
                'message' => 'Emel pengesahan telah dihantar jika akaun wujud.',
            ]);
        }

        if ($user->email_verified_at) {
            return response()->json([
                'message' => 'Emel anda telah disahkan. Sila log masuk.',
            ]);
        }

        $verificationToken = Str::random(64);
        $user->update(['verification_token' => $verificationToken]);

        $this->sendVerificationEmail($user, $verificationToken);

        return response()->json([
            'message' => 'Emel pengesahan telah dihantar semula.',
        ]);
    }

    /**
     * Send verification email
     */
    private function sendVerificationEmail(User $user, string $token): void
    {
        $verifyUrl = config('app.frontend_url', 'http://localhost:5173') . '/verify-email?token=' . $token;
        
        Mail::send('emails.verify', [
            'user' => $user,
            'verifyUrl' => $verifyUrl,
        ], function ($message) use ($user) {
            $message->to($user->email)
                    ->subject('iRPM - Sahkan Emel Anda');
        });
    }

    /**
     * Logout user
     */
    public function logout(Request $request): JsonResponse
    {
        $user = $request->user();
        
        AuditLog::log($user->id, AuditLog::ACTION_LOGOUT, 'User', $user->id);
        
        $user->currentAccessToken()->delete();

        return response()->json(['message' => 'Berjaya log keluar.']);
    }

    /**
     * Get current user
     */
    public function me(Request $request): JsonResponse
    {
        $user = $request->user();
        
        return response()->json([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
            'subscription_plan' => $user->subscription_plan,
            'subscription_label' => $user->getSubscriptionLabel(),
            'subscription_expires_at' => $user->subscription_expires_at,
            'created_at' => $user->created_at,
        ]);
    }

    /**
     * Change password
     */
    public function changePassword(Request $request): JsonResponse
    {
        $request->validate([
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:6',
        ]);

        $user = $request->user();

        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'message' => 'Kata laluan semasa tidak tepat.',
            ], 422);
        }

        $user->update([
            'password' => Hash::make($request->new_password),
        ]);

        AuditLog::log($user->id, 'password_change', 'User', $user->id);

        return response()->json([
            'message' => 'Kata laluan berjaya ditukar.',
        ]);
    }

    /**
     * Request password reset
     */
    public function forgotPassword(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $user = User::where('email', $request->email)->first();

        // Always return success to prevent email enumeration
        if (!$user) {
            return response()->json([
                'message' => 'Jika emel wujud, pautan tetapan semula telah dihantar.',
            ]);
        }

        // Generate reset token
        $resetToken = Str::random(64);
        $user->update([
            'password_reset_token' => $resetToken,
            'password_reset_expires_at' => now()->addHour(),
        ]);

        // Send reset email
        $this->sendPasswordResetEmail($user, $resetToken);

        return response()->json([
            'message' => 'Jika emel wujud, pautan tetapan semula telah dihantar.',
        ]);
    }

    /**
     * Reset password with token
     */
    public function resetPassword(Request $request): JsonResponse
    {
        $request->validate([
            'token' => 'required|string',
            'password' => 'required|string|min:6|confirmed',
        ]);

        $user = User::where('password_reset_token', $request->token)
            ->where('password_reset_expires_at', '>', now())
            ->first();

        if (!$user) {
            return response()->json([
                'message' => 'Token tidak sah atau telah tamat tempoh.',
            ], 400);
        }

        $user->update([
            'password' => Hash::make($request->password),
            'password_reset_token' => null,
            'password_reset_expires_at' => null,
        ]);

        AuditLog::log($user->id, 'password_reset', 'User', $user->id);

        return response()->json([
            'message' => 'Kata laluan berjaya ditetapkan semula. Sila log masuk.',
        ]);
    }

    /**
     * Send password reset email
     */
    private function sendPasswordResetEmail(User $user, string $token): void
    {
        $resetUrl = config('app.frontend_url', 'http://localhost:5173') . '/reset-password?token=' . $token;
        
        Mail::send('emails.reset-password', [
            'user' => $user,
            'resetUrl' => $resetUrl,
        ], function ($message) use ($user) {
            $message->to($user->email)
                    ->subject('iRPM - Tetapkan Semula Kata Laluan');
        });
    }
}


