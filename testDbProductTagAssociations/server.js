require("dotenv").config();

const express = require("express");
const path = require("path");
const app = express();
const PORT = 3232;
const pool = require("./testDbConnection");
const jsonHelper = require("../jsonHelper");
const fs = require("fs");
const { performance } = require("perf_hooks");
const { match } = require("assert");

app.get("/", async (req, res) => {
  res.json({ working: true });
});

// Get a list of all products
app.get("/products", async (req, res) => {
  let conn;
  try {
    conn = await pool.getConnection();
    const rows = await conn.query("SELECT * FROM Products");
    return res.json({ rows });
  } catch (e) {
    console.log(e);
    return res.json({ e });
  }
});

// Get a list of all machines
app.get("/machines", async (req, res) => {
  let conn;
  try {
    conn = await pool.getConnection();
    const rows = await conn.query(
      "SELECT * FROM Products p WHERE p.is_machine = true",
    );
    return res.json({
      rows: rows.map((item) => {
        return { ...item, link: `/machineAndItems/${item.id}` };
      }),
    });
  } catch (e) {
    console.log(e);
    return res.json({ e });
  }
});

// Get a list of all non-machine products
app.get("/nonmachines", async (req, res) => {
  let conn;
  try {
    conn = await pool.getConnection();
    const rows = await conn.query(
      "SELECT * FROM Products p WHERE p.is_machine = false",
    );
    return res.json({ rows });
  } catch (e) {
    console.log(e);
    return res.json({ e });
  }
});

// Get a list of tags
app.get("/tags", async (req, res) => {
  let conn;
  try {
    conn = await pool.getConnection();
    const rows = await conn.query("SELECT * FROM Tags");
    return res.json({ rows });
  } catch (e) {
    console.log(e);
    return res.json({ e });
  }
});

// Get an item and associated tags
app.get("/itemAndTags/:id", async (req, res) => {
  let conn;
  const { id } = req.params;
  try {
    conn = await pool.getConnection();
    const rows = await conn.query(
      `
      SELECT 
        p.*, 
        (GROUP_CONCAT(t.name ORDER BY t.name))  as tags
      FROM Products p 
      JOIN Product_Tags pt 
        ON pt.product_id = p.id 
      JOIN Tags t
        ON t.id = pt.tag_id
      WHERE p.id = ?
      GROUP BY p.id
    `,
      [id],
    );
    return res.json({ rows });
  } catch (e) {
    console.log(e);
    return res.json({ e });
  }
});

// Get machine and all compatible items
app.get("/machineAndItems/:id", async (req, res) => {
  const start = performance.now();
  let conn;
  const { id } = req.params;
  try {
    conn = await pool.getConnection();
    const [machineRows, partRows] = await Promise.all([
      conn.query(
        `SELECT
            p.id, p.name, p.sku, p.price,
            GROUP_CONCAT(t.name ORDER BY t.name SEPARATOR ', ') AS tags
        FROM Products p
        LEFT JOIN Product_Tags pt ON pt.product_id = p.id
        LEFT JOIN Tags t ON t.id = pt.tag_id
        WHERE p.id = ? AND p.is_machine = 1
        GROUP BY p.id`,
        [id],
      ),
      conn.query(
        `SELECT DISTINCT part.id, part.name, part.sku, part.price,
          GROUP_CONCAT(t.name ORDER BY t.name SEPARATOR ', ') AS matching_tags

        FROM Product_Tags pt1
        JOIN Product_Tags pt2 ON pt2.tag_id = pt1.tag_id
        JOIN Products part ON part.id = pt2.product_id AND part.is_machine = 0
        JOIN Tags t ON t.id = pt1.tag_id
        WHERE pt1.product_id = ?
        GROUP BY part.id
        ORDER BY part.name`,
        [id],
      ),
    ]);
    const durationMs = performance.now() - start;

    return res.json({
      queryTimeMs: durationMs.toFixed(2),
      machineRows,
      partRows: partRows.map((part) => {
        return { ...part, matchingMachines: `/itemAndMachines/${part.id}` };
      }),
    });
  } catch (e) {
    console.log(e);
    return res.json({ e });
  }
});

// Get item and all compatible machines
app.get("/itemAndMachines/:id", async (req, res) => {
  const start = performance.now();
  let conn;
  const { id } = req.params;
  try {
    conn = await pool.getConnection();
    const [partRows, machineRows] = await Promise.all([
      conn.query(
        `SELECT
            p.id, p.name, p.sku, p.price,
            GROUP_CONCAT(t.name ORDER BY t.name SEPARATOR ', ') AS tags
        FROM Products p
        LEFT JOIN Product_Tags pt ON pt.product_id = p.id
        LEFT JOIN Tags t ON t.id = pt.tag_id
        WHERE p.id = ? AND p.is_machine = 0
        GROUP BY p.id`,
        [id],
      ),
      conn.query(
        `SELECT DISTINCT machine.id, machine.name, machine.sku, machine.price,
          GROUP_CONCAT(t.name ORDER BY t.name SEPARATOR ', ') AS matching_tags

        FROM Product_Tags pt1
        JOIN Product_Tags pt2 ON pt2.tag_id = pt1.tag_id
        JOIN Products machine ON machine.id = pt2.product_id AND machine.is_machine = 1
        JOIN Tags t ON t.id = pt1.tag_id
        WHERE pt1.product_id = ?
        GROUP BY machine.id
        ORDER BY machine.name`,
        [id],
      ),
    ]);
    const durationMs = performance.now() - start;

    return res.json({
      queryTimeMs: durationMs.toFixed(2),
      partRows,
      machineRows: machineRows.map((machine) => {
        return {
          ...machine,
          matchingMachines: `/machineAndItems/${machine.id}`,
        };
      }),
    });
  } catch (e) {
    console.log(e);
    return res.json({ e });
  }
});

app.listen(PORT, () => {
  console.log(`Express server running on http://localhost:${PORT}`);
});
