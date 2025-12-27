const API_URL = 'http://localhost:8000/api' //read from .env

// Get auth token
function getToken() {
  return localStorage.getItem('token')
}

// API request helper
async function request(endpoint, options = {}) {
  const token = getToken()
  
  const config = {
    headers: {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
      ...(token && { 'Authorization': `Bearer ${token}` }),
    },
    ...options,
  }
  
  const response = await fetch(`${API_URL}${endpoint}`, config)
  
  if (response.status === 401) {
    localStorage.removeItem('token')
    localStorage.removeItem('user')
    window.location.href = '/login'
    throw new Error('Unauthorized')
  }
  
  const data = await response.json()
  
  if (!response.ok) {
    throw new Error(data.message || 'Request failed')
  }
  
  return data
}

// Auth API
export const auth = {
  async login(email, password) {
    const data = await request('/auth/login', {
      method: 'POST',
      body: JSON.stringify({ email, password }),
    })
    localStorage.setItem('token', data.token)
    localStorage.setItem('user', JSON.stringify(data.user))
    return data
  },
  
  async register(name, email, password, password_confirmation) {
    const data = await request('/auth/register', {
      method: 'POST',
      body: JSON.stringify({ name, email, password, password_confirmation }),
    })
    // Only store token if provided (bypass mode)
    if (data.token) {
      localStorage.setItem('token', data.token)
      localStorage.setItem('user', JSON.stringify(data.user))
    }
    return data
  },
  
  async verifyEmail(token) {
    const data = await request('/auth/verify-email', {
      method: 'POST',
      body: JSON.stringify({ token }),
    })
    if (data.token) {
      localStorage.setItem('token', data.token)
      localStorage.setItem('user', JSON.stringify(data.user))
    }
    return data
  },
  
  async resendVerification(email) {
    return request('/auth/resend-verification', {
      method: 'POST',
      body: JSON.stringify({ email }),
    })
  },
  
  async forgotPassword(email) {
    return request('/auth/forgot-password', {
      method: 'POST',
      body: JSON.stringify({ email }),
    })
  },
  
  async resetPassword(token, password, password_confirmation) {
    return request('/auth/reset-password', {
      method: 'POST',
      body: JSON.stringify({ token, password, password_confirmation }),
    })
  },
  
  async logout() {
    await request('/auth/logout', { method: 'POST' })
    localStorage.removeItem('token')
    localStorage.removeItem('user')
  },
  
  async getMe() {
    return request('/auth/me')
  },
  
  async changePassword(currentPassword, newPassword) {
    return request('/auth/change-password', {
      method: 'POST',
      body: JSON.stringify({
        current_password: currentPassword,
        new_password: newPassword,
      }),
    })
  },
  
  getUser() {
    const user = localStorage.getItem('user')
    return user ? JSON.parse(user) : null
  },
}

// Teacher API
export const teacher = {
  // Classes
  async getClasses() {
    return request('/teacher/classes')
  },
  
  async createClass(name, year) {
    return request('/teacher/classes', {
      method: 'POST',
      body: JSON.stringify({ name, year }),
    })
  },
  
  async getClass(id) {
    return request(`/teacher/classes/${id}`)
  },
  
  async getTodayClasses() {
    return request('/teacher/today-classes')
  },
  
  // Schedules
  async getSchedules() {
    return request('/teacher/schedules')
  },
  
  async createSchedule(classId, subjectId, dayOfWeek, startTime, endTime = null) {
    return request('/teacher/schedules', {
      method: 'POST',
      body: JSON.stringify({
        class_id: classId,
        subject_id: subjectId,
        day_of_week: dayOfWeek,
        start_time: startTime,
        end_time: endTime,
      }),
    })
  },
  
  async updateSchedule(id, data) {
    return request(`/teacher/schedules/${id}`, {
      method: 'PUT',
      body: JSON.stringify(data),
    })
  },
  
  async deleteSchedule(id) {
    return request(`/teacher/schedules/${id}`, { method: 'DELETE' })
  },
  
  async getCurrentSchedule() {
    return request('/teacher/schedules/current')
  },
  
  async getMySubjects() {
    return request('/teacher/schedules/my-subjects')
  },
  
  // Students
  async getStudents(classId) {
    return request(`/teacher/classes/${classId}/students`)
  },
  
  async addStudent(classId, ic, name, schoolName = null) {
    return request(`/teacher/classes/${classId}/students`, {
      method: 'POST',
      body: JSON.stringify({ ic, name, school_name: schoolName }),
    })
  },
  
  async lookupStudentByIc(ic) {
    return request(`/teacher/students/lookup?ic=${ic}`)
  },
  
  // Evaluations
  async getLatestTp(classId, subjectId, subtopicId) {
    return request(`/teacher/evaluations/latest?class_id=${classId}&subject_id=${subjectId}&subtopic_id=${subtopicId}`)
  },
  
  async saveTp(classId, subjectId, subtopicId, assessmentMethodId, evaluations) {
    return request('/teacher/evaluations', {
      method: 'POST',
      body: JSON.stringify({
        class_id: classId,
        subject_id: subjectId,
        subtopic_id: subtopicId,
        assessment_method_id: assessmentMethodId,
        evaluations,
      }),
    })
  },
  
  // Progress
  async getProgress(classId, subjectId) {
    return request(`/teacher/classes/${classId}/progress?subject_id=${subjectId}`)
  },
  
  async getSummary(classId, subjectId) {
    return request(`/teacher/classes/${classId}/summary?subject_id=${subjectId}`)
  },
  
  async getReport(classId, subjectId) {
    return request(`/teacher/classes/${classId}/report?subject_id=${subjectId}`)
  },
  
  async exportCsv(classId, subjectId) {
    const token = localStorage.getItem('token')
    const response = await fetch(`http://localhost:8000/api/teacher/classes/${classId}/export-csv?subject_id=${subjectId}`, {
      headers: {
        'Authorization': `Bearer ${token}`,
      },
    })
    
    if (!response.ok) {
      throw new Error('Export gagal')
    }
    
    // Get filename from header or generate
    const disposition = response.headers.get('Content-Disposition')
    let filename = 'laporan.csv'
    if (disposition) {
      const match = disposition.match(/filename="(.+)"/)
      if (match) filename = match[1]
    }
    
    // Download blob
    const blob = await response.blob()
    const url = window.URL.createObjectURL(blob)
    const a = document.createElement('a')
    a.href = url
    a.download = filename
    document.body.appendChild(a)
    a.click()
    window.URL.revokeObjectURL(url)
    a.remove()
  },
  
  async getStudentProgress(studentId, subjectId) {
    return request(`/teacher/students/${studentId}/progress?subject_id=${subjectId}`)
  },
}

// Subjects API
export const subjects = {
  async getAll() {
    return request('/subjects')
  },
  
  async getTopics(subjectId, year) {
    return request(`/subjects/${subjectId}/topics?year=${year}`)
  },
}

// Admin API
export const admin = {
  async getUsers(params = {}) {
    const query = new URLSearchParams(params).toString()
    return request(`/admin/users?${query}`)
  },
  
  async getUser(id) {
    return request(`/admin/users/${id}`)
  },
  
  async getStats() {
    return request('/admin/users/stats')
  },
  
  async updateSubscription(userId, plan, months = null) {
    return request(`/admin/users/${userId}/subscription`, {
      method: 'PUT',
      body: JSON.stringify({ subscription_plan: plan, months }),
    })
  },
  
  async toggleActive(userId) {
    return request(`/admin/users/${userId}/toggle-active`, {
      method: 'POST',
    })
  },

  // DSKP Management
  async getLevels() {
    return request('/admin/dskp/levels')
  },

  async getSubjectsByLevel(level) {
    return request(`/admin/dskp/levels/${level}/subjects`)
  },

  async getSubjectDetail(subjectId) {
    return request(`/admin/dskp/subjects/${subjectId}`)
  },

  async storeSubject(data) {
    return request('/admin/dskp/subjects', {
      method: 'POST',
      body: JSON.stringify(data),
    })
  },

  async deleteSubject(subjectId) {
    return request(`/admin/dskp/subjects/${subjectId}`, {
      method: 'DELETE',
    })
  },

  async storeTopic(data) {
    return request('/admin/dskp/topics', {
      method: 'POST',
      body: JSON.stringify(data),
    })
  },

  async storeSubtopic(data) {
    return request('/admin/dskp/subtopics', {
      method: 'POST',
      body: JSON.stringify(data),
    })
  },

  async previewDskp(file) {
    const formData = new FormData()
    formData.append('file', file)
    
    const token = localStorage.getItem('token')
    const response = await fetch(API_URL + '/admin/dskp/preview', {
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${token}`,
      },
      body: formData,
    })
    
    if (!response.ok) {
      const error = await response.json()
      throw new Error(error.message || 'Preview failed')
    }
    
    return response.json()
  },

  async importDskp(subjectId, file, academicYear = null) {
    const formData = new FormData()
    formData.append('subject_id', subjectId)
    formData.append('file', file)
    if (academicYear) {
      formData.append('academic_year', academicYear)
    }
    
    const token = localStorage.getItem('token')
    const response = await fetch(API_URL + '/admin/dskp/import', {
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${token}`,
      },
      body: formData,
    })
    
    if (!response.ok) {
      const error = await response.json()
      throw new Error(error.message || 'Import failed')
    }
    
    return response.json()
  },

  async updateTopic(id, data) {
    return request(`/admin/dskp/topics/${id}`, {
      method: 'PUT',
      body: JSON.stringify(data),
    })
  },

  async deleteTopic(id) {
    return request(`/admin/dskp/topics/${id}`, {
      method: 'DELETE',
    })
  },

  async updateSubtopic(id, data) {
    return request(`/admin/dskp/subtopics/${id}`, {
      method: 'PUT',
      body: JSON.stringify(data),
    })
  },

  async deleteSubtopic(id) {
    return request(`/admin/dskp/subtopics/${id}`, {
      method: 'DELETE',
    })
  },

  getTemplateUrl() {
    return API_URL + '/admin/dskp/template'
  },
}

