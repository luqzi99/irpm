const API_URL = 'http://localhost:8000/api'

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
    localStorage.setItem('token', data.token)
    localStorage.setItem('user', JSON.stringify(data.user))
    return data
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
  
  getExportCsvUrl(classId, subjectId) {
    const token = getToken()
    return `http://localhost:8000/api/teacher/classes/${classId}/export-csv?subject_id=${subjectId}&token=${token}`
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
