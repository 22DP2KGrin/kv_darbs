// Utility functions for handling exercise results

const EXERCISE_TOPIC_MAP = {
    'basic-vocabulary': { topicId: 1, exerciseType: 'vocabulary' },
    'present-simple': { topicId: 2, exerciseType: 'grammar' },
    'mydaily-routine': { topicId: 3, exerciseType: 'reading' },
    'introducing-yourself': { topicId: 4, exerciseType: 'writing' },
    'opinion-essay': { topicId: 5, exerciseType: 'writing' },
    'idiomatic-expressions': { topicId: 6, exerciseType: 'vocabulary' },
    'conditionals-wishes': { topicId: 7, exerciseType: 'grammar' },
    'city-vs-country': { topicId: 8, exerciseType: 'reading' },
    'phrasal-verbs': { topicId: 9, exerciseType: 'vocabulary' },
    'present-perfect-vs-past-simple': { topicId: 10, exerciseType: 'grammar' },
    'french-basic-vocabulary': { topicId: 11, exerciseType: 'vocabulary' },
    'french-present-simple': { topicId: 12, exerciseType: 'grammar' },
    'french-mydaily-routine': { topicId: 13, exerciseType: 'reading' },
    'french-introducing-yourself': { topicId: 14, exerciseType: 'writing' },
    'french-passe-compose': { topicId: 33, exerciseType: 'grammar' },
    'french-travel-vocabulary': { topicId: 34, exerciseType: 'vocabulary' },
    'french-restaurant-dialogues': { topicId: 35, exerciseType: 'reading' },
    'french-opinion-connectors': { topicId: 36, exerciseType: 'writing' },
    'french-subjunctive-basics': { topicId: 37, exerciseType: 'grammar' },
    'french-idiomatic-expressions-advanced': { topicId: 38, exerciseType: 'vocabulary' },
    'french-formal-email': { topicId: 39, exerciseType: 'writing' },
    'french-news-and-debate': { topicId: 40, exerciseType: 'reading' },
    'spanish-basic-vocabulary': { topicId: 15, exerciseType: 'vocabulary' },
    'spanish-present-simple': { topicId: 16, exerciseType: 'grammar' },
    'spanish-mydaily-routine': { topicId: 17, exerciseType: 'reading' },
    'spanish-introducing-yourself': { topicId: 18, exerciseType: 'writing' },
    'spanish-past-tense': { topicId: 23, exerciseType: 'grammar' },
    'spanish-travel-vocabulary': { topicId: 24, exerciseType: 'vocabulary' },
    'spanish-restaurant-dialogues': { topicId: 25, exerciseType: 'reading' },
    'spanish-opinion-connectors': { topicId: 26, exerciseType: 'writing' },
    'spanish-subjunctive-basics': { topicId: 27, exerciseType: 'grammar' },
    'spanish-idiomatic-expressions-advanced': { topicId: 28, exerciseType: 'vocabulary' },
    'spanish-formal-email': { topicId: 29, exerciseType: 'writing' },
    'spanish-news-and-debate': { topicId: 30, exerciseType: 'reading' },
    'latvian-basic-vocabulary': { topicId: 19, exerciseType: 'vocabulary' },
    'latvian-present-simple': { topicId: 20, exerciseType: 'grammar' },
    'latvian-mydaily-routine': { topicId: 21, exerciseType: 'reading' },
    'latvian-introducing-yourself': { topicId: 22, exerciseType: 'writing' }
};

// Get exercise ID from URL
function getExerciseId() {
    const urlParams = new URLSearchParams(window.location.search);
    const slug = urlParams.get('slug');
    if (slug) {
        return slug;
    }
    const pathParts = window.location.pathname.split('/');
    const exerciseName = pathParts[pathParts.length - 1].replace('.html', '');
    return exerciseName;
}

// Calculate total time spent on exercise
function calculateTotalTime(startTime) {
    if (!startTime) return 0;
    return Math.floor((Date.now() - startTime) / 1000);
}

// Save exercise results to server
async function saveExerciseResults(exerciseData) {
    try {
        const sessionToken = localStorage.getItem('userSessionToken');
        if (!sessionToken) {
            throw new Error('No session token found. Please log in to save your results.');
        }

        const exerciseId = getExerciseId();
        const exerciseConfig = EXERCISE_TOPIC_MAP[exerciseId];
        if (!exerciseConfig) {
            throw new Error(`Unknown exercise mapping for ${exerciseId}`);
        }

        const response = await fetch('../api/save_test_result.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Session-Token': sessionToken
            },
            body: JSON.stringify({
                result_type: 'exercise',
                topic_id: exerciseConfig.topicId,
                exercise_slug: exerciseId,
                exercise_type: exerciseData.exerciseType || exerciseConfig.exerciseType,
                ...exerciseData
            })
        });

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        const data = await response.json();
        if (!data.success) {
            console.error('Error saving exercise results:', data.error);
            return false;
        }
        return true;
    } catch (error) {
        console.error('Error submitting exercise results:', error);
        return false;
    }
}

// Save multiple choice test results
async function saveMultipleChoiceResults(questions, answers, startTime) {
    const correctAnswers = questions.reduce((count, question, index) => {
        return count + (answers[index] === question.correctAnswer ? 1 : 0);
    }, 0);

    const answersData = questions.map((question, index) => ({
        question_id: index + 1,
        question_text: question.question,
        user_answer: answers[index] !== null ? question.options[answers[index]] : null,
        correct_answer: question.options[question.correctAnswer] ?? null,
        is_correct: answers[index] === question.correctAnswer
    }));

    return await saveExerciseResults({
        score: correctAnswers,
        max_score: questions.length,
        time_spent: calculateTotalTime(startTime),
        answers: answersData
    });
}

// Save essay results
async function saveEssayResults(essayText, topic, startTime) {
    return await saveExerciseResults({
        exerciseType: 'writing',
        score: 1,
        max_score: 1,
        time_spent: calculateTotalTime(startTime),
        content_text: essayText,
        answers: [
            {
                question_id: 1,
                question_text: topic,
                user_answer: essayText,
                correct_answer: null,
                is_correct: null
            }
        ]
    });
}

// Save introduction results
async function saveIntroductionResults(introductionText, startTime) {
    return await saveExerciseResults({
        exerciseType: 'writing',
        score: 1,
        max_score: 1,
        time_spent: calculateTotalTime(startTime),
        content_text: introductionText,
        answers: [
            {
                question_id: 1,
                question_text: 'Introducing Yourself',
                user_answer: introductionText,
                correct_answer: null,
                is_correct: null
            }
        ]
    });
}

// Show success notification
function showSuccessNotification(message) {
    const notification = document.createElement('div');
    notification.className = 'notification success';
    notification.textContent = message;
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.remove();
    }, 3000);
}

// Show error notification
function showErrorNotification(message) {
    const notification = document.createElement('div');
    notification.className = 'notification error';
    notification.textContent = message;
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.remove();
    }, 3000);
}

// Add notification styles
const style = document.createElement('style');
style.textContent = `
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

    .notification.success {
        background-color: #10b981;
    }

    .notification.error {
        background-color: #ef4444;
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
document.head.appendChild(style); 
