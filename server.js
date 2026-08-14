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

app.get("/test/:id", async (req, res) => {
  const { id } = req.params;
  console.log("test route hit");
  let connection;

  try {
    // 1. Get connection from pool
    connection = await pool.getConnection();

    // 2. Run a simple query to test (don't just stringify the connection object)
    const rows = await connection.query(
      `
    SELECT 
        sc.id, 
        sc.pagetitle, 
        tvcv13.value AS categories_search_value,
        tvcv8.value AS associated_machines_value
    FROM 
        site_content sc 
    
    LEFT JOIN 
        site_tmplvar_contentvalues tvcv13 
    ON 
        tvcv13.tmplvarid = 13 
    AND 
        sc.id = tvcv13.contentid 
    
    LEFT JOIN
        site_tmplvar_contentvalues tvcv8
    ON
        tvcv8.tmplvarid = 8
    AND
        sc.id = tvcv8.contentid
    WHERE 
        sc.id = ?
    `,
      [id],
    );

    // 8885 sample id
    res.json(rows);
    // [
    //   {
    //     id: 8885,
    //     pagetitle: "Simplicity Flash Multi-Use Handheld Vacuum",
    //     categories_search_value:
    //       "Associated Machines==%SIM->F1.6;%||Associated Machines==%GROUP-All-Vacuums;%",
    //     associated_machines_value: null,
    //   },
    // ];

    // OR

    // [
    //   {
    //     id: 8913,
    //     pagetitle: "Mettler-Metrosene 100% Polyester 8 Thread Gift Pack",
    //     categories_search_value: null,
    //     associated_machines_value:
    //       "GROUP-Thread-Sewing; GROUP-Thread-Quilting;",
    //   },
    // ];

    // now have to split and parse the value
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
