const express = require('express');
const app = express();
app.use(express.json());

app.post('/api/register', (req, res) => {
    const { email, password } = req.body;

    if (!email || !password) {
        return res.status(400).json({ error: 'Email and password are required' });
    }

    // Пример проверки email
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(email)) {
        return res.status(400).json({ error: 'Invalid email format' });
    }

    // Логика сохранения пользователя в базе данных
    // Например:
    // db.users.insert({ email, password });

    res.status(201).json({ message: 'User registered successfully' });
});

app.listen(3000, () => console.log('Server running on http://localhost:3000'));
