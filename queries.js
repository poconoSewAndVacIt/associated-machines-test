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
          (tv13.value != "" OR tv13.value IS NOT NULL)
          OR 
          (tv8.value != "" OR tv8.value IS NOT NULL) 
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
          (tv8.value != "" OR tv8.value IS NOT NULL) 
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
          (tv13.value != "" OR tv13.value IS NOT NULL)
        )
    `;

const SEARCH_FOR_ITEMS_QUERY = `
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
    `;
const SEARCH_FOR_ITEM_QUERY = SEARCH_FOR_ITEMS_QUERY + ` LIMIT 1`;

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
        sc.deleted = 0

    AND (
          tv13.value != "" OR 
          tv13.value IS NOT NULL OR 
          tv8.value != "" OR 
          tv8.value IS NOT NULL
        )

    LIMIT 1
    `;

module.exports = {
  PRODUCT_COUNT_QUERY,
  PART_COUNT_QUERY,
  MACHINE_COUNT_QUERY,
  SEARCH_FOR_ITEMS_QUERY,
  SEARCH_FOR_ITEM_QUERY,
  ALL_PRODUCTS_QUERY,
};
