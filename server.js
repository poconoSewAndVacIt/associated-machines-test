require("dotenv").config();

const express = require("express");
const path = require("path");
const app = express();
const PORT = 3000;
const pool = require("./dbConnection");

// Serve your frontend HTML/CSS files from a folder named "public"
app.use(express.static(path.join(__dirname, "public")));

app.get("/api/data", (req, res) => {
  res.json({ message: "Hello from the backend!" });
});

app.get("/associations/:id", (req, res) => {
  const { id } = req.params;
  res.json({ id });
});

app.get("/test", async (req, res) => {
  console.log("test route hit");
  let connection;

  try {
    // 1. Get connection from pool
    connection = await pool.getConnection();

    // 2. Run a simple query to test (don't just stringify the connection object)
    const rows = await connection.query("SELECT 1 + 1 AS result");
    res.json(rows);
  } catch (error) {
    console.error("Database error details:", error);
    res
      .status(500)
      .json({ error: "Database connection failed", details: error.message });
  } finally {
    // 3. ALWAYS release the connection back to the pool, even if it fails
    if (connection) connection.release();
  }
});

app.listen(PORT, () => {
  console.log(`Express server running on http://localhost:${PORT}`);
});
