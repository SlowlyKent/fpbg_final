const express = require('express');
const app = express();
const cors = require('cors');
const bodyParser = require('body-parser');
const db = require('./db'); // Assuming you have a db.js file for database connection

app.use(cors());
app.use(bodyParser.json());

app.get('/api/notifications', (req, res) => {
  db.query('SELECT * FROM notifications', (err, results) => {
    if (err) {
      console.error('Error fetching notifications:', err);
      res.status(500).send('Error fetching notifications');
    } else {
      res.json(results);
    }
  });
});

app.listen(3001, () => {
  console.log('Server running on port 3001');
});
