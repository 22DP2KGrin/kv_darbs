async function saveTestResult(testData) {
    const sessionToken = localStorage.getItem('userSessionToken');
    if (!sessionToken) {
        throw new Error('No session token found. Please log in to save your results.');
    }

    const resultData = {
        result_type: testData.resultType || 'test',
        topic_id: testData.topicId,
        topic_key: testData.topicKey || null,
        score: testData.score,
        max_score: testData.maxScore,
        time_spent: testData.timeSpent,
        exercise_slug: testData.exerciseSlug || null,
        exercise_type: testData.exerciseType || null,
        content_text: testData.contentText || null,
        errors: (testData.errors || []).map(error => ({
            question_id: error.questionId,
            user_answer: error.userAnswer,
            correct_answer: error.correctAnswer,
            question_text: error.questionText
        })),
        answers: (testData.answers || []).map(answer => ({
            question_id: answer.questionId,
            question_text: answer.questionText,
            user_answer: answer.userAnswer,
            correct_answer: answer.correctAnswer,
            is_correct: answer.isCorrect
        }))
    };

    try {
        const response = await fetch('../api/save_test_result.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Session-Token': sessionToken
            },
            body: JSON.stringify(resultData)
        });

        if (!response.ok) {
            const errorText = await response.text();
            console.error('Server response:', errorText);
            throw new Error(`Servera kļūda: ${response.status} ${response.statusText}`);
        }

        let data;
        try {
            data = await response.json();
        } catch (e) {
            console.error('Failed to parse JSON response:', e);
            throw new Error('Serveris atgrieza nekorektu atbildi');
        }

        if (!data.success) {
            if (data.error === 'User not authenticated') {
                localStorage.removeItem('userSessionToken');
                window.location.href = '../login.html';
                return;
            }
            throw new Error(data.error || 'Neizdevās saglabāt testa rezultātu');
        }

        return data;
    } catch (error) {
        console.error('Error saving test result:', error);
        throw error;
    }
}

async function finishTest() {
    const testData = {
        topicId: getTopicId(),
        score: calculateScore().correctAnswers,
        maxScore: questions.length,
        timeSpent: calculateTotalTime(),
        errors: getTestErrors()
    };

    try {
        await saveTestResult(testData);
        displayResult(testData);
    } catch (error) {
        console.error('Error finishing test:', error);
        alert('Failed to save test result. Please try again.');
    }
}

function getTestErrors() {
    return questions.map((question, index) => {
        const userAnswer = getUserAnswer(index);
        const isCorrect = userAnswer === question.correctAnswer;
        
        return {
            questionId: question.id,
            userAnswer: userAnswer,
            correctAnswer: question.correctAnswer,
            questionText: question.question,
            isCorrect: isCorrect
        };
    }).filter(error => !error.isCorrect);
}

function getTopicId() {
    const urlParams = new URLSearchParams(window.location.search);
    const topicId = urlParams.get('topic_id');
    if (!topicId) {
        throw new Error('Topic ID not found');
    }
    return topicId;
} 
