// Language test functionality

// Add notification styles
const notificationStyles = document.createElement('style');
notificationStyles.textContent = `
    .notification {
        position: fixed;
        top: 20px;
        right: 20px;
        padding: 15px 25px;
        border-radius: 5px;
        color: white;
        font-weight: 500;
        z-index: 1000;
        animation: slideIn 0.3s ease-out;
    }

    .notification.error {
        background-color: #ef4444;
    }

    .notification.success {
        background-color: #10b981;
    }

    @keyframes slideIn {
        from {
            transform: translateX(100%);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
`;
document.head.appendChild(notificationStyles);

document.addEventListener('DOMContentLoaded', function() {
    // Get language from URL
    const pathParts = window.location.pathname.split('/');
    const language = pathParts[pathParts.length - 2];
    
    // Load test questions
    const questions = getTestQuestions(language);
    
    // Initialize test state
    let currentQuestion = 0;
    let selectedAnswers = {};
    let testCompleted = false;
    
    // Render first question
    renderQuestion(questions[currentQuestion], currentQuestion, questions.length);
    
    // Set up modal for finishing early
    setupFinishEarlyModal();
    
    // Function to render a question
    function renderQuestion(question, index, total) {
        startQuestionTimer(index);
        const testContent = document.getElementById('test-content');
        const questionNumber = document.getElementById('question-number');
        const progressPercentage = document.getElementById('progress-percentage');
        const progressFill = document.querySelector('.progress-fill');
        
        // Update progress indicators
        questionNumber.textContent = `Question ${index + 1} of ${total}`;
        const percentage = Math.round(((index + 1) / total) * 100);
        progressPercentage.textContent = `${percentage}% complete`;
        progressFill.style.width = `${percentage}%`;
        
        // Create question HTML
        let html = `
            <div class="question-card">
                <h3 class="question-title">${question.text}</h3>
                <div class="options-list">
        `;
        
        // Add options
        question.options.forEach(option => {
            const isSelected = selectedAnswers[question.id] === option.id;
            html += `
                <div class="option-item ${isSelected ? 'selected' : ''}" data-option-id="${option.id}">
                    <input type="radio" id="option-${option.id}" name="question-${question.id}" class="option-radio" ${isSelected ? 'checked' : ''}>
                    <label for="option-${option.id}">${option.text}</label>
                </div>
            `;
        });
        
        // Add navigation buttons
        html += `
                </div>
                <div class="test-actions">
                    <button id="prevBtn" class="btn btn-outline" ${index === 0 ? 'disabled' : ''}>
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
                        Previous
                    </button>
                    <button id="finishEarlyBtn" class="btn btn-outline">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon"><path d="M4 15s1-1 4-1 5 2 8 2 4-1 4-1V3s-1 1-4 1-5-2-8-2-4 1-4 1z"></path><line x1="4" y1="22" x2="4" y2="15"></line></svg>
                        Finish Test
                    </button>
                    <button id="nextBtn" class="btn btn-primary">
                        ${index < total - 1 ? 'Next' : 'Finish Test'}
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
                    </button>
                </div>
            </div>
        `;
        
        testContent.innerHTML = html;
        
        // Add event listeners
        setupQuestionEventListeners(questions, currentQuestion);
    }
    
    // Set up event listeners for question
    function setupQuestionEventListeners(questions, index) {
        const optionItems = document.querySelectorAll('.option-item');
        const prevBtn = document.getElementById('prevBtn');
        const nextBtn = document.getElementById('nextBtn');
        const finishEarlyBtn = document.getElementById('finishEarlyBtn');
        
        // Option selection
        optionItems.forEach(item => {
            item.addEventListener('click', function() {
                const optionId = this.getAttribute('data-option-id');
                const questionId = questions[index].id;
                
                // Update selected answer
                selectedAnswers[questionId] = optionId;
                
                // Update UI
                optionItems.forEach(opt => opt.classList.remove('selected'));
                this.classList.add('selected');
                
                // Update radio button
                const radio = this.querySelector('input[type="radio"]');
                radio.checked = true;
            });
        });
        
        // Previous button
        if (prevBtn) {
            prevBtn.addEventListener('click', function() {
                if (index > 0) {
                    endQuestionTimer(index);
                    currentQuestion--;
                    renderQuestion(questions[currentQuestion], currentQuestion, questions.length);
                }
            });
        }
        
        // Next button
        if (nextBtn) {
            nextBtn.addEventListener('click', function() {
                if (index < questions.length - 1) {
                    endQuestionTimer(index);
                    currentQuestion++;
                    renderQuestion(questions[currentQuestion], currentQuestion, questions.length);
                } else {
                    endQuestionTimer(index);
                    showResults(questions);
                }
            });
        }
        
        // Finish early button
        if (finishEarlyBtn) {
            finishEarlyBtn.addEventListener('click', function() {
                const modal = document.getElementById('finishEarlyModal');
                const modalMessage = document.getElementById('modal-message');
                
                // Update modal message
                const answeredCount = Object.keys(selectedAnswers).length;
                modalMessage.textContent = `Are you sure you want to finish the test now? You've answered ${answeredCount} out of ${questions.length} questions.`;
                
                if (answeredCount < questions.length) {
                    modalMessage.textContent += ' Unanswered questions will be marked as incorrect.';
                }
                
                // Show modal
                modal.style.display = 'block';
            });
        }
    }
    
    // Set up finish early modal
    function setupFinishEarlyModal() {
        const modal = document.getElementById('finishEarlyModal');
        const closeModal = document.querySelector('.close-modal');
        const continueTestBtn = document.getElementById('continueTestBtn');
        const finishNowBtn = document.getElementById('finishNowBtn');
        
        // Close modal
        if (closeModal) {
            closeModal.addEventListener('click', function() {
                modal.style.display = 'none';
            });
        }
        
        // Continue test
        if (continueTestBtn) {
            continueTestBtn.addEventListener('click', function() {
                modal.style.display = 'none';
            });
        }
        
        // Finish now
        if (finishNowBtn) {
            finishNowBtn.addEventListener('click', function() {
                modal.style.display = 'none';
                showResults(questions);
            });
        }
        
        // Close modal when clicking outside
        window.addEventListener('click', function(event) {
            if (event.target === modal) {
                modal.style.display = 'none';
            }
        });
    }
    
    // Show test results
    async function showResults(questions) {
        // Скрываем элементы теста
        document.getElementById('test-content').style.display = 'none';
        document.getElementById('progress-container').style.display = 'none';
        document.getElementById('timer-container').style.display = 'none';
        
        // Показываем контейнер результатов
        const resultsContainer = document.getElementById('results-container');
        resultsContainer.style.display = 'block';
        
        // Подсчитываем результаты
        const score = calculateScore(questions, selectedAnswers);
        const maxScore = questions.length;
        const timeSpent = calculateTotalTime();
        const level = calculateLevel(questions, selectedAnswers);
        
        // Собираем информацию об ошибках
        const errors = [];
        questions.forEach((question, index) => {
            const userAnswer = selectedAnswers[question.id];
            if (userAnswer !== question.correctAnswer) {
                errors.push({
                    question_id: index + 1,
                    question_text: question.text,
                    user_answer: userAnswer,
                    correct_answer: question.correctAnswer
                });
            }
        });
        
        // Подготавливаем данные для отправки
        const testData = {
            test_id: getTestType(),
            score: score.percentage,
            max_score: maxScore,
            time_spent: timeSpent,
            errors: errors
        };
        
        try {
            // Отправляем результаты на сервер
            const response = await fetch('save_test_result.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(testData)
            });
            
            const result = await response.json();
            
            if (!result.success) {
                throw new Error(result.error || 'Failed to save test results');
            }
            
            // Отображаем результаты
            displayResults(score.percentage, maxScore, timeSpent, level, errors);
            
            // Показываем уведомление об успехе
            showNotification('Test results saved successfully!', 'success');
            
        } catch (error) {
            console.error('Error saving test results:', error);
            showNotification('Failed to save test results. Please try again.', 'error');
            
            // Все равно показываем результаты, даже если не удалось сохранить
            displayResults(score.percentage, maxScore, timeSpent, level, errors);
        }
    }
    
    function displayResults(score, maxScore, timeSpent, level, errors) {
        const resultsContainer = document.getElementById('results-container');
        const percentage = Math.round((score / maxScore) * 100);
        
        // Форматируем время
        const minutes = Math.floor(timeSpent / 60);
        const seconds = timeSpent % 60;
        const timeString = `${minutes}m ${seconds}s`;
        
        // Создаем HTML для результатов
        let resultsHTML = `
            <div class="results-summary">
                <h2>Test Results</h2>
                <div class="result-item">
                    <span class="result-label">Score:</span>
                    <span class="result-value">${score}/${maxScore} (${percentage}%)</span>
                </div>
                <div class="result-item">
                    <span class="result-label">Time Spent:</span>
                    <span class="result-value">${timeString}</span>
                </div>
                <div class="result-item">
                    <span class="result-label">Level:</span>
                    <span class="result-value">${level}</span>
                </div>
            </div>
        `;
        
        // Добавляем секцию с ошибками, если они есть
        if (errors.length > 0) {
            resultsHTML += `
                <div class="errors-section">
                    <h3>Questions to Review</h3>
                    <div class="errors-list">
            `;
            
            errors.forEach(error => {
                resultsHTML += `
                    <div class="error-item">
                        <p class="question-text">${error.question_text}</p>
                        <div class="answer-comparison">
                            <div class="answer-item">
                                <span class="label">Your answer:</span>
                                <span class="value wrong">${error.user_answer}</span>
                            </div>
                            <div class="answer-item">
                                <span class="label">Correct answer:</span>
                                <span class="value correct">${error.correct_answer}</span>
                            </div>
                        </div>
                    </div>
                `;
            });
            
            resultsHTML += `
                    </div>
                </div>
            `;
        }
        
        // Добавляем кнопки действий
        resultsHTML += `
            <div class="results-actions">
                <button onclick="location.reload()" class="btn retry-btn">Try Again</button>
                <button onclick="window.location.href='dashboard.php'" class="btn dashboard-btn">Go to Dashboard</button>
            </div>
        `;
        
        resultsContainer.innerHTML = resultsHTML;
    }
    
    // Calculate score
    function calculateScore(questions, answers) {
        let correct = 0;
        let answered = 0;
        
        questions.forEach(q => {
            if (answers[q.id]) {
                answered++;
                if (answers[q.id] === q.correctAnswer) {
                    correct++;
                }
            }
        });
        
        return {
            correct,
            answered,
            total: questions.length,
            percentage: answered > 0 ? Math.round((correct / answered) * 100) : 0
        };
    }
    
    // Calculate level
    function calculateLevel(questions, answers) {
        let beginnerCorrect = 0;
        let intermediateCorrect = 0;
        let advancedCorrect = 0;
        
        questions.forEach(q => {
            if (answers[q.id] === q.correctAnswer) {
                if (q.level === 'Beginner') beginnerCorrect++;
                else if (q.level === 'Intermediate') intermediateCorrect++;
                else if (q.level === 'Advanced') advancedCorrect++;
            }
        });
        
        if (advancedCorrect >= 1) return 'Advanced';
        if (intermediateCorrect >= 1) return 'Intermediate';
        return 'Beginner';
    }

    // Helper function to get exercise ID
    function getExerciseId() {
        const pathParts = window.location.pathname.split('/');
        const exerciseName = pathParts[pathParts.length - 2];
        return exerciseName;
    }

    // Helper function to calculate total time
    function calculateTotalTime() {
        let totalTime = 0;
        questions.forEach(question => {
            if (question.timeSpent) {
                totalTime += question.timeSpent;
            }
        });
        return totalTime;
    }

    // Add time tracking to questions
    function startQuestionTimer(questionId) {
        if (!questions[questionId].startTime) {
            questions[questionId].startTime = Date.now();
        }
    }

    function endQuestionTimer(questionId) {
        if (questions[questionId].startTime) {
            const timeSpent = Math.floor((Date.now() - questions[questionId].startTime) / 1000);
            questions[questionId].timeSpent = timeSpent;
        }
    }

    // Helper function to get test type
    function getTestType() {
        const pathParts = window.location.pathname.split('/');
        const language = pathParts[pathParts.length - 2];
        return `${language}_test`;
    }

    // Helper function to show error notification
    function showErrorNotification(message) {
        const notification = document.createElement('div');
        notification.className = 'notification error';
        notification.textContent = message;
        document.body.appendChild(notification);
        
        setTimeout(() => {
            notification.remove();
        }, 3000);
    }

    // Helper function to show notification
    function showNotification(message, type) {
        const notification = document.createElement('div');
        notification.className = `notification ${type}`;
        notification.textContent = message;
        document.body.appendChild(notification);
        
        setTimeout(() => {
            notification.remove();
        }, 3000);
    }
});

// Get test questions for a language
function getTestQuestions(language) {
    const testQuestions = {
        english: [
            {
                id: 1,
                text: "Choose the correct form of the verb: She ___ to the store yesterday.",
                options: [
                    { id: "a", text: "go" },
                    { id: "b", text: "goes" },
                    { id: "c", text: "went" },
                    { id: "d", text: "going" }
                ],
                correctAnswer: "c",
                level: "Beginner"
            },
            {
                id: 2,
                text: "Which sentence is grammatically correct?",
                options: [
                    { id: "a", text: "I have been to Paris last year." },
                    { id: "b", text: "I went to Paris last year." },
                    { id: "c", text: "I have gone to Paris last year." },
                    { id: "d", text: "I am going to Paris last year." }
                ],
                correctAnswer: "b",
                level: "Intermediate"
            },
            {
                id: 3,
                text: "Choose the correct meaning of the idiom: 'To bite the bullet'",
                options: [
                    { id: "a", text: "To be very angry" },
                    { id: "b", text: "To face a difficult situation bravely" },
                    { id: "c", text: "To make a mistake" },
                    { id: "d", text: "To eat something hard" }
                ],
                correctAnswer: "b",
                level: "Advanced"
            }
        ],
        latvian: [
            {
                id: 1,
                text: "Izvēlieties pareizo vietniekvārdu: ___ ir mana māsa.",
                options: [
                    { id: "a", text: "Viņš" },
                    { id: "b", text: "Viņa" },
                    { id: "c", text: "Viņi" },
                    { id: "d", text: "Viņas" }
                ],
                correctAnswer: "b",
                level: "Beginner"
            },
            {
                id: 2,
                text: "Izvēlieties pareizo darbības vārda formu: Es ___ grāmatu.",
                options: [
                    { id: "a", text: "lasīt" },
                    { id: "b", text: "lasu" },
                    { id: "c", text: "lasīju" },
                    { id: "d", text: "lasīšu" }
                ],
                correctAnswer: "b",
                level: "Beginner"
            },
            {
                id: 3,
                text: "Izvēlieties pareizo locījumu: Es dzīvoju ___ Rīgā.",
                options: [
                    { id: "a", text: "iekšā" },
                    { id: "b", text: "iekš" },
                    { id: "c", text: "iekšā Rīgā" },
                    { id: "d", text: "iekš Rīgā" }
                ],
                correctAnswer: "d",
                level: "Intermediate"
            },
            {
                id: 4,
                text: "Kāds ir pareizais tulkojums frāzes 'To bite the bullet'?",
                options: [
                    { id: "a", text: "Kost lodi" },
                    { id: "b", text: "Būt ļoti dusmīgam" },
                    { id: "c", text: "Izciest grūtības" },
                    { id: "d", text: "Kļūt par karavīru" }
                ],
                correctAnswer: "c",
                level: "Advanced"
            }
        ],
        french: [
            {
                id: 1,
                text: "Choisissez le bon article: ___ chat est noir.",
                options: [
                    { id: "a", text: "Un" },
                    { id: "b", text: "Une" },
                    { id: "c", text: "Le" },
                    { id: "d", text: "La" }
                ],
                correctAnswer: "c",
                level: "Beginner"
            },
            {
                id: 2,
                text: "Conjuguez le verbe 'être' au présent: Je ___ étudiant.",
                options: [
                    { id: "a", text: "suis" },
                    { id: "b", text: "es" },
                    { id: "c", text: "est" },
                    { id: "d", text: "sont" }
                ],
                correctAnswer: "a",
                level: "Beginner"
            },
            {
                id: 3,
                text: "Choisissez la bonne forme du passé composé: Hier, je ___ au cinéma.",
                options: [
                    { id: "a", text: "suis allé" },
                    { id: "b", text: "vais" },
                    { id: "c", text: "irai" },
                    { id: "d", text: "allais" }
                ],
                correctAnswer: "a",
                level: "Intermediate"
            },
            {
                id: 4,
                text: "Quelle est la signification de l'expression 'Avoir le cafard'?",
                options: [
                    { id: "a", text: "Avoir un insecte" },
                    { id: "b", text: "Être déprimé" },
                    { id: "c", text: "Boire du café" },
                    { id: "d", text: "Avoir peur" }
                ],
                correctAnswer: "b",
                level: "Advanced"
            }
        ],
        spanish: [
            {
                id: 1,
                text: "Elige el artículo correcto: ___ libro es interesante.",
                options: [
                    { id: "a", text: "Un" },
                    { id: "b", text: "Una" },
                    { id: "c", text: "El" },
                    { id: "d", text: "La" }
                ],
                correctAnswer: "c",
                level: "Beginner"
            },
            {
                id: 2,
                text: "Conjuga el verbo 'ser' en presente: Yo ___ estudiante.",
                options: [
                    { id: "a", text: "soy" },
                    { id: "b", text: "eres" },
                    { id: "c", text: "es" },
                    { id: "d", text: "son" }
                ],
                correctAnswer: "a",
                level: "Beginner"
            },
            {
                id: 3,
                text: "Elige la forma correcta del pretérito: Ayer, yo ___ al cine.",
                options: [
                    { id: "a", text: "fui" },
                    { id: "b", text: "voy" },
                    { id: "c", text: "iré" },
                    { id: "d", text: "iba" }
                ],
                correctAnswer: "a",
                level: "Intermediate"
            },
            {
                id: 4,
                text: "¿Qué significa la expresión 'Estar en las nubes'?",
                options: [
                    { id: "a", text: "Estar muy feliz" },
                    { id: "b", text: "Estar distraído" },
                    { id: "c", text: "Estar en un avión" },
                    { id: "d", text: "Estar confundido" }
                ],
                correctAnswer: "b",
                level: "Advanced"
            }
        ]
    };
    
    // Return questions for the specified language or default questions
    return testQuestions[language] || [
        {
            id: 1,
            text: "This is a basic question to test your knowledge.",
            options: [
                { id: "a", text: "Option A" },
                { id: "b", text: "Option B" },
                { id: "c", text: "Option C" },
                { id: "d", text: "Option D" }
            ],
            correctAnswer: "c",
            level: "Beginner"
        },
        {
            id: 2,
            text: "This is an intermediate level question.",
            options: [
                { id: "a", text: "Option A" },
                { id: "b", text: "Option B" },
                { id: "c", text: "Option C" },
                { id: "d", text: "Option D" }
            ],
            correctAnswer: "b",
            level: "Intermediate"
        },
        {
            id: 3,
            text: "This is an advanced level question.",
            options: [
                { id: "a", text: "Option A" },
                { id: "b", text: "Option B" },
                { id: "c", text: "Option C" },
                { id: "d", text: "Option D" }
            ],
            correctAnswer: "a",
            level: "Advanced"
        }
    ];
}