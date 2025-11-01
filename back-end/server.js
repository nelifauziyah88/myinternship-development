const express = require('express');
const cors = require('cors');
const db = require('./db');
const authRoutes = require('./routes/auth');
const StudentRoutes = require('./routes/student');
const lecturerRoutes = require('./routes/lecturer');
const cdcRoutes = require('./routes/cdc');
const kampusRoutes = require('./routes/kampus');

const app = express();
const PORT = 8000;

// Middleware
app.use(cors());
app.use(express.json());
app.use(express.urlencoded({ extended: true }));

// Routes
app.use('/api', authRoutes);
app.use('/api/student', StudentRoutes);
app.use('/api', lecturerRoutes);
app.use('/api', cdcRoutes);
app.use('/api/kampus', kampusRoutes);

// Server
app.listen(PORT, () => {
  console.log(`Server running on http://localhost:${PORT}`);
});
