update `iap_catalog`
set `cat_item_code` = 
(
    select `NewName` from `iap_catfix` 
	where `cat_item_code` = `OldName`
	and `OldName` > ""
)
where `cat_item_code` like "CUST%";

update `iap_catalog`
set `cat_description` = 
(
    select `NewDesc` from `iap_catfix` 
	where `cat_item_code` = `NewName`
)
where `cat_item_code` like "CUST%";

update `iap_inventory`
set `inv_item_code` = 
(
    select `NewName` from `iap_catfix` 
	where `inv_item_code` = `OldName`
	and `OldName` > ""
)
where `inv_item_code` like "CUST%";

update `iap_inventory`
set `inv_on_hand` = 
(
    select `NewQTY` from `iap_catfix` 
	where `inv_item_code` = `NewName`
)
where `inv_item_code` like "CUST%";

update `iap_prices`
set `prc_item_code` = 
(
    select `NewName` from `iap_catfix` 
	where `prc_item_code` = `OldName`
	and `OldName` > ""
)
where `prc_item_code` like "CUST%";

update `iap_purchase_detail`
set `purdet_item` = 
(
    select `NewName` from `iap_catfix` 
	where `purdet_item` = `OldName`
	and `OldName` > ""
)
where `purdet_item` like "CUST%";

update `iap_purchase_lots`
set `lot_item_code` = 
(
    select `NewName` from `iap_catfix` 
	where `lot_item_code` = `OldName`
	and `OldName` > ""
)
where `lot_item_code` like "CUST%";
