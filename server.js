require("dotenv").config();

const express = require("express");
const path = require("path");
const app = express();
const PORT = 3000;
const pool = require("./dbConnection");
const jsonHelper = require("./jsonHelper");
const fs = require("fs");
const { match } = require("assert");

const {
  PRODUCT_COUNT_QUERY,
  MACHINE_COUNT_QUERY,
  PART_COUNT_QUERY,
} = require("./queries.js");

const {
  findMachinesByTagOrGroup,
  findPartsByTagOrGroup,
  searchForItemsById,
  searchForItemById,
  makeAllProductsQueryWithLimit,
  searchForItemsByTag,
} = require("./queryFunctions.js");

const {
  parseMachineTagsGroups,
  parsePartAssociatedTagsGroups,
  printAllRoutes,
} = require("./helpers.js");

// Fix Big Int Bug
BigInt.prototype.toJSON = function () {
  // Option A: Convert to a string (Safest for massive numbers)
  return this.toString();

  // Option B: Convert to a regular number (Use ONLY if values fit within standard JS numbers)
  // return Number(this);
};

// Serve your frontend HTML/CSS files from a folder named "public"
app.use(express.static(path.join(__dirname, "public")));

// machineExamples: [9, 18, 23, 24, 27],
// partExamples: [5253, 31, 34, 35, 38, 39],

// This gets a count of products, and of machines/parts
// http://localhost:3000/productCount
app.get("/productCount", async (req, res) => {
  const connection = await pool.getConnection();

  await Promise.all([
    connection.query(PRODUCT_COUNT_QUERY),
    connection.query(MACHINE_COUNT_QUERY),
    connection.query(PART_COUNT_QUERY),
  ])
    // destructure the array response
    .then(([product_count, machine_count, part_count]) =>
      res.json({
        product_count,
        machine_count,
        part_count,
      }),
    )
    .catch((error) => {
      console.log(error);
      res
        .status(500)
        .json({ status: "Error", code: 500, message: error.message });
    });
  if (connection) connection.release();
});

// This gets a product list, you add your own limit
// http://localhost:3000/products
app.get("/products", async (req, res) => {
  let { limit } = req.query;

  // prevent SQL injection from query
  if (!(Number(limit) >= 1)) limit = 1;

  let connection;
  try {
    connection = await pool.getConnection();
    const query = makeAllProductsQueryWithLimit(limit);
    const data = await connection.query(query);

    res.json({
      count: data.length,
      note: "You can add ?limit=X to the end of the url to change the number of items queried. (i.e. /products?limit=100",
      data,
    });
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

// This gets EVERYTHING
// http://localhost:3000/allProducts
app.get("/allProducts", async (req, res) => {
  const limit = 1000000;

  let connection;
  try {
    connection = await pool.getConnection();
    const query = makeAllProductsQueryWithLimit(limit);
    const data = await connection.query(query);

    res.json({
      count: data.length,
      note: "You can add ?limit=X to the end of the url to change the number of items queried. (i.e. /products?limit=100",
      data,
    });
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

// @#@#@# this code can probably be deleted, it was used to make a local copy of modx's custom snippet code for easy review
// app.get("/snippets", async (req, res) => {
//   return res.send(
//     "this endpoint has been turned off. comment out this line to turn back on",
//   );
//   let connection;
//   try {
//     connection = await pool.getConnection();
//     const data = await connection.query(`
//             SELECT id, name, snippet FROM site_snippets;

//             `);
//     for (let d of data) {
//       let fileName = `./snippets/${d.id}-${d.name}.php`;
//       try {
//         fs.writeFileSync(fileName, d.snippet, "utf8");
//       } catch (error) {
//         console.log(error);
//         res.status(500).json({ error: "Error saving", details: error.message });
//       }
//     }
//     res.json(data);
//   } catch (error) {
//     console.error("Database Snippet Error details:", error);
//     res
//       .status(500)
//       .json({ error: "Database connection failed", details: error.message });
//   } finally {
//     if (connection) connection.release();
//   }
// });

// Make this one only get parts by id
// Example: http://localhost:3000/part/26
app.get("/part{/:partId}", async (req, res) => {
  const { partId } = req.params;

  // Guard against incorrect url's
  if (!(partId >= 0))
    return res.json({ error: "needs a partId", example: "/part/26" });

  let connection;
  try {
    connection = await pool.getConnection();

    const data = await connection.query(
      `
    SELECT 
        sc.id,
        sc.pagetitle, sc.longtitle, sc.description, sc.alias, sc.introtext, sc.content, sc.createdon, sc.editedon, sc.deleted, sc.publishedon, sc.published, sc.uri, sc.properties,
        tv8.value as TV8_VALUE
        
    FROM 
        site_content as sc

    LEFT JOIN
        site_tmplvar_contentvalues as tv8
    ON
        tv8.tmplvarid = 8
    and
        sc.id = tv8.contentid
    
    WHERE
        sc.id = ?

    LIMIT 1
    `,
      [partId],
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

// Make this one only get a machine by id
// Example: http://localhost:3000/machine/484
app.get("/machine{/:machineId}", async (req, res) => {
  const { machineId } = req.params;

  // Guard against incorrect url's
  if (!(machineId >= 0))
    return res.json({ error: "needs a machineId", example: "/machine/9" });

  let connection;
  try {
    connection = await pool.getConnection();

    const data = await connection.query(
      `
    SELECT 
        sc.id,
        sc.pagetitle, sc.longtitle, sc.description, sc.alias, sc.introtext, sc.content, sc.createdon, sc.editedon, sc.deleted, sc.publishedon, sc.published, sc.uri, sc.properties,
        tv13.value as TV13_VALUE
        
    FROM 
        site_content as sc

    LEFT JOIN
        site_tmplvar_contentvalues as tv13
    ON
        tv13.tmplvarid = 13
    and
        sc.id = tv13.contentid
    
    WHERE
        sc.id = ?

    LIMIT 1
    `,
      [machineId],
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

// You can search for a tag and it'll bring up lists of matching matchines and matching parts
// Example: http://localhost:3000/productsWithTag/PFA-%3EPFAFF%20Creative%207530
app.get("/productsWithTag{/:tag}", async (req, res) => {
  const { tag } = req.params;
  console.log("tag", tag);
  if (!tag) {
    return res.json({
      error: "needs a tag",
      example: "/productsWithTag/PFA->PFAFF Creative 7530",
    });
  }

  let connection;
  try {
    connection = await pool.getConnection();
    const rows = await searchForItemsByTag(tag, connection);
    console.log(rows);
    // return res.json(rows);
    const result = {
      count: { machines: 0, parts: 0 },
      machines: [],
      parts: [],
    };
    for (let row of rows) {
      if (row.TV13_VALUE) {
        result.parts.push(row);
      } else if (row.TV8_VALUE) {
        result.machines.push(row);
      }
    }
    result.count.machines = result.machines.length;
    result.count.parts = result.parts.length;

    res.json(result);
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

// This route is more generic in that it can grab any product plus associated parts/machines
app.get("/product{/:id}", async (req, res) => {
  const { id } = req.params;

  // Guard against incorrect url's
  if (!(id >= 0))
    return res.json({
      error: "needs a product id",
      example: "/product/18",
      example2: "/product/26",
    });
  console.log("test route hit", id);

  let connection;

  try {
    connection = await pool.getConnection();

    const rows = await searchForItemById(id, connection);
    let matchingParts = null;

    const item = rows[0];
    if (!item) return res.json({ message: "no part found" });
    // @#@#@# maybe change the "as X" of this query for consistent labeling
    const isMachine = item.machine_tags_groups !== null;
    const isPart = item.associated_tags_groups !== null;

    if (isMachine) {
      // Find matching parts
      const tagsAndGroups = parseMachineTagsGroups(item.machine_tags_groups);

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
      const tagsAndGroups = parsePartAssociatedTagsGroups(
        item.associated_tags_groups,
      );

      let matchingMachines = await findMachinesByTagOrGroup(
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
  printAllRoutes(app);
  console.log(`Express server running on http://localhost:${PORT}`);
});
