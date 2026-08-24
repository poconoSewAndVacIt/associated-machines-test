const {
  SEARCH_FOR_ITEM_QUERY,
  SEARCH_FOR_ITEMS_QUERY,
  ALL_PRODUCTS_QUERY,
} = require("./queries.js");

async function findMachinesByTagOrGroup(tagGroup, connection) {
  const whereClause = tagGroup.map(() => `tvcv13.value LIKE ?`).join(" OR ");
  const tagsWithWildcards = tagGroup.map((item) => `%${item}%`);
  const fullQuery = `
    SELECT
    sc.id,
    sc.pagetitle,
    sc.uri,
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

async function findPartsByTagOrGroup(tagGroup, connection) {
  //   makes a bunch of x.value LIKE ? OR ...
  // this maps against the tagGroup array
  const whereClause = tagGroup.map(() => `tvcv8.value LIKE ?`).join(" OR ");

  const tagsWithWildcards = tagGroup.map((item) => `%${item}%`);

  const fullQuery = `
    SELECT
    sc.id,
    sc.pagetitle,
    sc.uri,
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

async function searchForItemsById(id, connection) {
  return await connection.query(SEARCH_FOR_ITEMS_QUERY, [id]);
}
async function searchForItemById(id, connection) {
  return await connection.query(SEARCH_FOR_ITEM_QUERY, [id]);
}

function printAllRoutes(app) {
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
function makeAllProductsQueryWithLimit(limit = 1) {
  return ALL_PRODUCTS_QUERY.replace("LIMIT 1", `LIMIT ${limit}`);
}

module.exports = {
  findMachinesByTagOrGroup,
  findPartsByTagOrGroup,
  printAllRoutes,
  searchForItemsById,
  searchForItemById,
  makeAllProductsQueryWithLimit,
};
