SELECT `cat_item_code`, " --- ", `cat_description`, `cat_on_hand`, 
       `inv_on_hand`,
       `prc_cat_code`, `prc_units`, `prc_cost_unit`, `prc_price`

FROM `iap_catalog` 

JOIN iap_inventory ON `inv_item_code` = `cat_item_code`

JOIN iap_prices ON `prc_item_code` = `cat_item_code` AND `prc_effective_until` = "2099-12-31"

WHERE `cat_company` = 5

ORDER BY `cat_item_code`
