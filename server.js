require("dotenv").config();

const express = require("express");
const path = require("path");
const app = express();
const PORT = 3000;
const pool = require("./dbConnection");
const jsonHelper = require("./jsonHelper");
const fs = require("fs");
const { match } = require("assert");

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

// @#@#@# get all snippets and save them
app.get("/snippets", async (req, res) => {
  return res.send(
    "this endpoint has been turned off. comment out this line to turn back on",
  );
  let connection;
  try {
    connection = await pool.getConnection();
    const data = await connection.query(`
            SELECT id, name, snippet FROM site_snippets;
            
            `);
    for (let d of data) {
      let fileName = `./snippets/${d.id}-${d.name}.php`;
      try {
        fs.writeFileSync(fileName, d.snippet, "utf8");
      } catch (error) {
        console.log(error);
        res.status(500).json({ error: "Error saving", details: error.message });
      }
    }
    res.json(data);
  } catch (error) {
    console.error("Database Snippet Error details:", error);
    res
      .status(500)
      .json({ error: "Database connection failed", details: error.message });
  } finally {
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
    connection = await pool.getConnection();

    const rows = await searchForItemsById(id, connection);
    let matchingParts = null;

    const item = rows[0];
    const isMachine = item.machine_tags_groups !== null;
    const isPart = item.associated_tags_groups !== null;

    if (isMachine) {
      // Find matching parts
      const tagsAndGroups = item.machine_tags_groups
        .replaceAll(/\\r|\\n/g, "")
        .replaceAll("Associated Machines", "")
        .replaceAll(/[%\|=]/g, "")
        .split(";")
        .map((item) => item.trim())
        .filter((item) => item.length);

      matchingParts = await findPartsByTagOrGroup(tagsAndGroups, connection);

      matchingParts = matchingParts.map((part) => {
        const matchesOn = tagsAndGroups.filter((tag) =>
          part.associated_tags_groups.includes(tag),
        );
        return { ...part, matchesOn };
      });
      res.json({ item, matchingParts });
    } else if (isPart) {
      // Find matching matchines
      const tagsAndGroups = item.associated_tags_groups
        .replaceAll(/\\r|\\n/g, "")
        .split(";")
        .map((item) => item.trim())
        .filter((item) => item.length);
      console.log(tagsAndGroups);

      matchingMachines = await findMachinesByTagOrGroup(
        tagsAndGroups,
        connection,
      );

      console.log(matchingMachines.length);

      matchingMachines = matchingMachines.map((machine) => {
        const matchesOn = tagsAndGroups.filter((tag) =>
          machine.associated_machine_tags_groups.includes(tag),
        );
        return { ...machine, matchesOn };
      });
      res.json({ item, matchingMachines });
    }

    // 8885 sample id
    // [
    //   {
    //     id: 8885,
    //     pagetitle: "Simplicity Flash Multi-Use Handheld Vacuum",
    //     machine_tags_groups:
    //       "Associated Machines==%SIM->F1.6;%||Associated Machines==%GROUP-All-Vacuums;%",
    //     associated_tags_groups: null,
    //   },
    // ];

    // OR

    // [
    //   {
    //     id: 8913,
    //     pagetitle: "Mettler-Metrosene 100% Polyester 8 Thread Gift Pack",
    //     machine_tags_groups: null,
    //     associated_tags_groups:
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
  printAllRoutes();
  console.log(`Express server running on http://localhost:${PORT}`);
});

function printAllRoutes() {
  console.log("All Routes:");
  for (let d of app.router.stack) {
    if (d.route) {
      // Routes registered directly on the app (e.g., app.get('/users'))
      const methods = Object.keys(d.route.methods).join(", ").toUpperCase();
      console.log(`[${methods}] ${d.route.path}`);
    } else if (d.name === "router") {
      // Routes registered via express.Router()
      d.handle.stack.forEach((handler) => {
        if (handler.route) {
          const methods = Object.keys(handler.route.methods)
            .join(", ")
            .toUpperCase();
          // Combines the router base path with the route path
          const basePath = d.regexp.source
            .replace(/\\\//g, "/")
            .replace(/\?\:\(\?\=\\\/\|\$\)/g, "");
          console.log(`[${methods}] ${basePath}${handler.route.path}`);
        }
      });
    }
  }
}

async function searchForItemsById(id, connection) {
  return await connection.query(
    `
    SELECT 
        sc.id, 
        sc.pagetitle, 
        tvcv10.value AS product_setting_value,
        tvcv13.value AS machine_tags_groups,
        tvcv8.value AS associated_tags_groups
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
}

async function findPartsByTagOrGroup(tagGroup, connection) {
  //   makes a bunch of x.value LIKE ? OR ...
  // this maps against the tagGroup array
  const whereClause = tagGroup.map(() => `tvcv8.value LIKE ?`).join(" OR ");

  const tagsWithWildcards = tagGroup.map((item) => `%${item}%`);

  const fullQuery = `
    SELECT
    sc.id,
    sc.pagetitle,
    tvcv8.value AS associated_tags_groups

    FROM
    site_content sc
    
    LEFT JOIN
    site_tmplvar_contentvalues tvcv8
    ON
    tvcv8.tmplvarid = 8
    AND
    sc.id = tvcv8.contentid
    
    WHERE ${whereClause}  
  `;

  return await connection.query(fullQuery, tagsWithWildcards);
}

async function findMachinesByTagOrGroup(tagGroup, connection) {
  const whereClause = tagGroup.map(() => `tvcv13.value LIKE ?`).join(" OR ");
  const tagsWithWildcards = tagGroup.map((item) => `%${item}%`);
  const fullQuery = `
    SELECT
    sc.id,
    sc.pagetitle,
    tvcv13.value AS associated_machine_tags_groups

    FROM
    site_content sc
    
    LEFT JOIN
    site_tmplvar_contentvalues tvcv13
    ON
    tvcv13.tmplvarid = 13
    AND
    sc.id = tvcv13.contentid
    
    WHERE ${whereClause}  
  `;

  return await connection.query(fullQuery, tagsWithWildcards);
}
