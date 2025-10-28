/**
 * Quiz System JavaScript
 * Handle quiz taking, timer, and submission
 */

let quizTimer = null;
let quizStartTime = null;
let attemptId = null;

document.addEventListener('DOMContentLoaded', function() {
    const quizContainer = document.getElementById('quizContainer');
    
    if (quizContainer) {
        initQuiz(quizContainer);
    }
});

/**
 * Initialize Quiz
 */
function initQuiz(container) {
    const quizId = container.dataset.quizId;
    const duration = parseInt(container.dataset.duration) * 60; // Convert to seconds
    
    // Start quiz
    startQuiz(quizId, duration);
    
    // Handle quiz submission
    const submitBtn = document.getElementById('submitQuizBtn');
    if (submitBtn) {
        submitBtn.addEventListener('click', function() {
            if (confirm('Bạn có chắc chắn muốn nộp bài?')) {
                submitQuiz();
            }
        });
    }
}

/**
 * Start Quiz
 */
function startQuiz(quizId, duration) {
    // Call API to start quiz attempt
    fetch('/api/quiz_api.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `action=start_quiz&quiz_id=${quizId}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            attemptId = data.attempt_id;
            quizStartTime = Date.now();
            
            // Display questions
            displayQuestions(data.questions);
            
            // Start timer
            startTimer(duration);
        } else {
            alert(data.message);
            window.location.href = '/student/my-courses.php';
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Có lỗi xảy ra khi bắt đầu quiz');
    });
}

/**
 * Display Questions
 */
function displayQuestions(questions) {
    const container = document.getElementById('questionsContainer');
    
    questions.forEach((question, index) => {
        const options = JSON.parse(question.options);
        
        const questionDiv = document.createElement('div');
        questionDiv.className = 'quiz-question';
        questionDiv.innerHTML = `
            <div class="question-header">
                <span class="question-number">Câu ${index + 1}</span>
                <span class="question-points">${question.points} điểm</span>
            </div>
            <div class="question-text">${question.question}</div>
            <div class="question-options">
                ${options.map(option => `
                    <label class="option-label">
                        <input type="radio" name="question_${question.id}" value="${option.id}" required>
                        <span>${option.text}</span>
                    </label>
                `).join('')}
            </div>
        `;
        
        container.appendChild(questionDiv);
    });
}

/**
 * Start Timer
 */
function startTimer(duration) {
    let timeRemaining = duration;
    const timerDisplay = document.getElementById('quizTimer');
    
    function updateTimer() {
        const minutes = Math.floor(timeRemaining / 60);
        const seconds = timeRemaining % 60;
        
        timerDisplay.textContent = `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
        
        // Change color when time is running out
        if (timeRemaining <= 60) {
            timerDisplay.classList.add('timer-warning');
        }
        if (timeRemaining <= 30) {
            timerDisplay.classList.add('timer-danger');
        }
        
        if (timeRemaining <= 0) {
            clearInterval(quizTimer);
            alert('Hết thời gian! Quiz sẽ được tự động nộp.');
            submitQuiz();
        }
        
        timeRemaining--;
    }
    
    updateTimer();
    quizTimer = setInterval(updateTimer, 1000);
}

/**
 * Submit Quiz
 */
function submitQuiz() {
    // Stop timer
    if (quizTimer) {
        clearInterval(quizTimer);
    }
    
    // Collect answers
    const answers = {};
    document.querySelectorAll('[name^="question_"]').forEach(radio => {
        if (radio.checked) {
            const questionId = radio.name.replace('question_', '');
            answers[questionId] = radio.value;
        }
    });
    
    // Calculate time spent
    const timeSpent = Math.floor((Date.now() - quizStartTime) / 1000);
    
    // Show loading
    const submitBtn = document.getElementById('submitQuizBtn');
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang xử lý...';
    submitBtn.disabled = true;
    
    // Submit to API
    const formData = new URLSearchParams();
    formData.append('action', 'submit_quiz');
    formData.append('attempt_id', attemptId);
    formData.append('answers', JSON.stringify(answers));
    formData.append('time_spent', timeSpent);
    
    fetch('/api/quiz_api.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Redirect to results page
            window.location.href = `/learning/quiz-result.php?attempt_id=${attemptId}`;
        } else {
            alert(data.message);
            submitBtn.innerHTML = 'Nộp bài';
            submitBtn.disabled = false;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Có lỗi xảy ra khi nộp bài');
        submitBtn.innerHTML = 'Nộp bài';
        submitBtn.disabled = false;
    });
}

/**
 * Quiz Navigation
 */
function initQuizNavigation() {
    const questions = document.querySelectorAll('.quiz-question');
    const navContainer = document.getElementById('quizNavigation');
    
    if (!navContainer) return;
    
    questions.forEach((question, index) => {
        const navBtn = document.createElement('button');
        navBtn.className = 'quiz-nav-btn';
        navBtn.textContent = index + 1;
        navBtn.addEventListener('click', function() {
            question.scrollIntoView({ behavior: 'smooth', block: 'center' });
            
            // Update active state
            document.querySelectorAll('.quiz-nav-btn').forEach(btn => {
                btn.classList.remove('active');
            });
            this.classList.add('active');
        });
        
        navContainer.appendChild(navBtn);
    });
    
    // Update nav buttons when answers are selected
    questions.forEach((question, index) => {
        const radios = question.querySelectorAll('input[type="radio"]');
        radios.forEach(radio => {
            radio.addEventListener('change', function() {
                const navBtn = navContainer.children[index];
                navBtn.classList.add('answered');
            });
        });
    });
}

/**
 * Auto-save Quiz Progress
 */
function initAutoSave() {
    let saveTimeout;
    
    document.querySelectorAll('[name^="question_"]').forEach(radio => {
        radio.addEventListener('change', function() {
            clearTimeout(saveTimeout);
            saveTimeout = setTimeout(() => {
                saveQuizProgress();
            }, 2000);
        });
    });
}

function saveQuizProgress() {
    if (!attemptId) return;

    // Collect current answers
    const answers = {};
    document.querySelectorAll('[name^="question_"]').forEach(radio => {
        if (radio.checked) {
            const questionId = radio.name.replace('question_', '');
            answers[questionId] = radio.value;
        }
    });

    const formData = new URLSearchParams();
    formData.append('action', 'save_progress');
    formData.append('attempt_id', attemptId);
    formData.append('answers', JSON.stringify(answers));

    fetch('/api/quiz_api.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: formData
    }).then(resp => resp.json())
    .then(data => {
        if (!data.success) {
            console.warn('Auto-save failed:', data.message);
        }
    }).catch(err => {
        console.error('Auto-save error:', err);
    });
}
