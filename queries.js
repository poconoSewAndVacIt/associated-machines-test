// TV8 is the field in modx DB than stores machine tags
// TV13 is the field in modx DB that stores part tags
// TV10 is the field in modx DB that stores product settings, like "discontinued" or "verifyfit" or both
// These are "Template Variables", basically free-floating data that associates itself with a type of data (the TV field, 8, 10, 13), and a document ID (for a product or page).

const PRODUCT_COUNT_QUERY = `
    SELECT 
      count(*)
        
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
        sc.deleted = 0
    AND (
          (tv13.value != "" AND tv13.value IS NOT NULL)
          OR 
          (tv8.value != "" AND tv8.value IS NOT NULL) 
        )
    `;
const MACHINE_COUNT_QUERY = `
    SELECT 
      count(*)
        
    FROM 
        site_content as sc
        
    LEFT JOIN
        site_tmplvar_contentvalues as tv8
    ON
        tv8.tmplvarid = 8
    AND
        sc.id =  tv8.contentid
    
    WHERE
        sc.deleted = 0
    AND (
          (tv8.value != "" AND tv8.value IS NOT NULL) 
        )
    `;

const PART_COUNT_QUERY = `
    SELECT 
      count(*)
        
    FROM 
        site_content as sc

    LEFT JOIN
        site_tmplvar_contentvalues as tv13
    ON
        tv13.tmplvarid = 13
    AND
        sc.id =  tv13.contentid
    
    WHERE
        sc.deleted = 0
    AND (
          (tv13.value != "" AND tv13.value IS NOT NULL)
        )
    `;

const SEARCH_FOR_ITEMS_QUERY = `
    SELECT 
        sc.id, 
        sc.pagetitle, 
        tv10.value AS product_setting_value,
        tv13.value AS machine_tags_groups,
        tv8.value AS associated_tags_groups
    FROM 
        site_content sc 
    
    LEFT JOIN 
        site_tmplvar_contentvalues tv13 
    ON 
        tv13.tmplvarid = 13 
    AND 
        sc.id = tv13.contentid 
    
    LEFT JOIN
        site_tmplvar_contentvalues tv10
    ON
        tv10.tmplvarid = 10
    AND
        sc.id = tv10.contentid

    LEFT JOIN
        site_tmplvar_contentvalues tv8
    ON
        tv8.tmplvarid = 8
    AND
        sc.id = tv8.contentid

    WHERE 
        sc.id = ?
    `;
const SEARCH_FOR_ITEM_QUERY = SEARCH_FOR_ITEMS_QUERY + ` LIMIT 1`;

const SEARCH_FOR_ITEMS_BY_TAG_QUERY = `
    SELECT 
        sc.id, 
        sc.pagetitle, 
        sc.uri,
        sc.published,
        sc.deleted,
        tv8.value AS TV8_VALUE,
        tv13.value AS TV13_VALUE
    FROM 
        site_content sc 

    LEFT JOIN
        site_tmplvar_contentvalues tv8
    ON
        tv8.tmplvarid = 8
    AND
        sc.id = tv8.contentid
    
    LEFT JOIN 
        site_tmplvar_contentvalues tv13 
    ON 
        tv13.tmplvarid = 13 
    AND 
        sc.id = tv13.contentid 

    WHERE 
        tv8.value LIKE ?
    OR
        tv13.value LIKE ?
`;

const ALL_PRODUCTS_QUERY = `
    SELECT 
        sc.id, 
        sc.pagetitle, 
        sc.longtitle,
        sc.description,
        sc.alias,
        sc.published,
        sc.pub_date,
        sc.introtext,
        sc.content,
        sc.createdon,
        sc.publishedon,
        sc.properties,
        sc.uri,
        sc.parent,
        tv8.value as TV8_VALUE,
        tv13.value as TV13_VALUE
        
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
        sc.deleted = 0

    AND (
          (tv13.value != "" AND 
          tv13.value IS NOT NULL) 
        OR 
          (tv8.value != "" AND 
          tv8.value IS NOT NULL)
        )

    LIMIT 1
    `;

module.exports = {
  ALL_PRODUCTS_QUERY,
  MACHINE_COUNT_QUERY,
  PART_COUNT_QUERY,
  PRODUCT_COUNT_QUERY,
  SEARCH_FOR_ITEM_QUERY,
  SEARCH_FOR_ITEMS_BY_TAG_QUERY,
  SEARCH_FOR_ITEMS_QUERY,
};
