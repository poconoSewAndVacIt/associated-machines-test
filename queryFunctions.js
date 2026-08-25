const {
  ALL_PRODUCTS_QUERY,
  SEARCH_FOR_ITEM_QUERY,
  SEARCH_FOR_ITEMS_BY_TAG_QUERY,
  SEARCH_FOR_ITEMS_QUERY,
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

function makeAllProductsQueryWithLimit(limit = 1) {
  return ALL_PRODUCTS_QUERY.replace("LIMIT 1", `LIMIT ${limit}`);
}

async function searchForItemsByTag(tag, connection) {
  const tagWithWildcard = `%${tag}%`;
  // Query requires two ? fill-ins so to match with TV8 and TV13
  return await connection.query(SEARCH_FOR_ITEMS_BY_TAG_QUERY, [
    tagWithWildcard,
    tagWithWildcard,
  ]);
}

module.exports = {
  findMachinesByTagOrGroup,
  findPartsByTagOrGroup,
  makeAllProductsQueryWithLimit,
  searchForItemById,
  searchForItemsById,
  searchForItemsByTag,
};
