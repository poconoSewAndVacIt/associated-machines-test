require("dotenv").config();

const express = require("express");
const path = require("path");
const app = express();
const PORT = 3000;
const pool = require("./dbConnection");

// Fix Big Int Bug
BigInt.prototype.toJSON = function () {
  // Option A: Convert to a string (Safest for massive numbers)
  return this.toString();

  // Option B: Convert to a regular number (Use ONLY if values fit within standard JS numbers)
  // return Number(this);
};

// Serve your frontend HTML/CSS files from a folder named "public"
app.use(express.static(path.join(__dirname, "public")));

app.get("/api/data", (req, res) => {
  res.json({ message: "Hello from the backend!" });
});

app.get("/product/:id", async (req, res) => {
  let connection;
  try {
    connection = await pool.getConnection();

    const data = await connection.query(
      `
    SELECT 
        sc.id, sc.pagetitle, tv13.value as TV13_VALUE
        
    FROM 
        site_content as sc

    LEFT JOIN
        site_tmplvar_contentvalues as tv13
    ON
        tv13.tmplvarid = 13
    AND
        sc.id =  tv13.contentid
    
    WHERE
        sc.parent = 11
    AND
        sc.deleted = 0

    LIMIT 100
    `,
    );

    res.json(data);
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

// Make this one only get machines
app.get("/machine/:machineId", async (req, res) => {
  const { machineId: id } = req.params;
  let connection;
  try {
    connection = await pool.getConnection();

    const machineData = await connection.query(
      `
    SELECT 
        sc.id, sc.pagetitle, 
        tv13.value as TV13_VALUE,
        tv8.value as TV8_VALUE
        
    FROM 
        site_content as sc

    LEFT JOIN
        site_tmplvar_contentvalues as tv13
    ON
        tv13.tmplvarid = 13
    AND
        sc.id =  tv13.contentid
    
    LEFT JOIN
        site_tmplvar_contentvalues as tv8
    ON
        tv8.tmplvarid = 8
    AND
        sc.id =  tv8.contentid
    
    WHERE
    # parent is not correct for some reason, maybe nested?
    #    sc.parent = 11
    # AND
        sc.deleted = 0
    AND 
        sc.id = ?

    LIMIT 100
    `,
      [id],
    );

    let associations = machineData.TV13_VALUE;

    res.json(machineData);
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

// Make this one only get parts
app.get("/part/:partId", async (req, res) => {
  let connection;
  try {
    connection = await pool.getConnection();

    const data = await connection.query(
      `
    SELECT 
        sc.id, sc.pagetitle, tv8.value as TV8_VALUE
        
    FROM 
        site_content as sc

    LEFT JOIN
        site_tmplvar_contentvalues as tv8
    ON
        tv8.tmplvarid = 8
    and
        sc.id = tv8.contentid
    
    WHERE
        sc.parent = 11

    LIMIT 100
    `,
    );

    res.json(data);
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

app.get("/allMachines", async (req, res) => {
  let connection;
  try {
    connection = await pool.getConnection();

    const data = await connection.query(
      `
    SELECT 
        sc.id, sc.pagetitle, tv13.value as TV13_VALUE, tv8.value as TV8_VALUE
        
    FROM 
        site_content as sc

    LEFT JOIN
        site_tmplvar_contentvalues as tv13
    ON
        tv13.tmplvarid = 13

    AND
        sc.id =  tv13.contentid

    LEFT JOIN
        site_tmplvar_contentvalues as tv8
    ON
        tv8.tmplvarid = 8
    and
        sc.id = tv8.contentid
    
    WHERE
        sc.parent = 11

    LIMIT 100
    `,
    );

    res.json(data);
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

app.get("/test/:id", async (req, res) => {
  const { id } = req.params;
  console.log("test route hit", id);
  let connection;

  try {
    // 1. Get connection from pool
    connection = await pool.getConnection();

    const justItem = await connection.query(
      `SELECT * FROM site_content WHERE id = ?`,
      [id],
    );

    console.log(justItem);

    // 2. Run a simple query to test (don't just stringify the connection object)
    // Also tvcv10 would have "verifyfit", but that might not be important if either 8/13 has a property.
    const rows = await connection.query(
      `
    SELECT 
        sc.id, 
        sc.pagetitle, 
        tvcv10.value AS product_setting_value,
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
        site_tmplvar_contentvalues tvcv10
    ON
        tvcv10.tmplvarid = 10
    AND
        sc.id = tvcv10.contentid

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

    res.json(rows);

    // 8885 sample id
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
