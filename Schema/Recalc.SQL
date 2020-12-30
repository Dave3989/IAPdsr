0) Checking From Set - S
SELECT `purdet_purid`, `purdet_item`, `purdet_date`
FROM `iap_recalc_purdet`
WHERE `purdet_from_set`
LIKE 'S' 
ORDER BY `purdet_purid` ASC

==========

1) Copy iap_sales_applied_lots to recalc_applot then add Sale_Date to AppLot <<< only once 
update `iap_recalc_applot`` 
set `sale_ymd` = 
 (
     select sale_date from iap_sales
     where sale_id = `saledet_sid`
	 limit 1
  )

-------------------------

1A) Load iap__recalc_applot_sum from iap_sales_detail and get stats
INSERT INTO iap_recalc_applot_sum
SELECT `saledet_item_code`, `saledet_lot_cost`, sum(`saledet_quantity`), "2014-12-31"
FROM `iap_recalc_saldet` 
JOIN iap_sales ON sale_id = saledet_sid
WHERE `saledet_company` = 5
AND `sale_type` <> "W"
AND YEAR(`sale_ymd`) = 2014
GROUP BY `saledet_item_code`, `saledet_lot_cost`;

SELECT COUNT(`salelot_item_code`) AS Records, SUM(`salelot_quantity`) AS Itens, SUM(`salelot_lot_cost` * `salelot_quantity`) AS Amount
FROM `iap_recalc_applot_sum` 
WHERE YEAR(`sale_ymd`) = 2014;

=================

1)  Load Purchase Detail for Year
insert into iap_recalc_purdet
SELECT `iap_purchase_detail`.* FROM `iap_purchase_detail` 
JOIN iap_supplier_prices ON `prc_item_code` = `purdet_item` 
 AND `prc_effective_until` = "2099-12-31" 
WHERE `purdet_company` = 5
AND YEAR(`purdet_date`) = 2014
AND purdet_from_set <> "S"
AND `prc_cat_code` <> "cat012"
AND `prc_cat_code` <> "cat014";
select count(*), SUM(`purdet_quantity`), SUm(`purdet_cost` * `purdet_quantity`) from iap_recalc_purdet
where YEAR(`purdet_date`) = 2014; 

---------------------------------------

2) Load Purchase Lots for Year 
insert into iap_recalc_purlot
SELECT `purdet_item`, `purdet_cost`, SUM(`purdet_quantity`), "2014-12-31"
FROM `iap_recalc_purdet` 
JOIN iap_supplier_prices ON `prc_item_code` = `purdet_item` 
 AND `prc_effective_until` = "2099-12-31" 
WHERE `purdet_company` = 5
AND YEAR(`purdet_date`) = 2014
AND purdet_from_set <> "S"
AND `prc_cat_code` <> "cat012"
AND `prc_cat_code` <> "cat014"
GROUP BY `purdet_item`, `purdet_cost`;

SELECT COUNT(*), SUM(lot_count), SUM(lot_cost * lot_count) 
FROM `iap_recalc_purlot`
WHERE YEAR(`lot_date`) = 2014;

================

3) Load iap__recalc_applot_sum from iap_sales_detail and get stats
INSERT INTO iap_recalc_applot_sum
SELECT `saledet_item_code`, `saledet_lot_cost`, sum(`saledet_quantity`), "2014-12-31"
FROM `iap_recalc_saldet` 
JOIN iap_sales ON sale_id = saledet_sid
WHERE `saledet_company` = 5
AND `sale_type` <> "W"
AND YEAR(`sale_ymd`) = 2014
GROUP BY `saledet_item_code`, `saledet_lot_cost`;

SELECT COUNT(*) AS Records, SUM(`salelot_quantity`) AS Itens, SUM(`salelot_lot_cost` * `salelot_quantity`) AS Amount
FROM `iap_recalc_applot_sum` 
WHERE YEAR(`sale_ymd`) = 2014;

=====================

4) Decrement Lots by Sold 
SELECT COUNT(*), SUM(`lot_count`), SUM(`lot_cost` * `lot_count`) 
    FROM `iap_recalc_purlot`
    WHERE YEAR(`lot_date`) = 2014;

SELECT COUNT(*), SUM(`salelot_quantity`), SUM(`salelot_quantity` * `salelot_lot_cost`) 
    FROM `iap_recalc_applot_sum` 
    WHERE YEAR(`sale_ymd`) = 2014;

TRUNCATE `iap_recalc_purlot2`;
insert into `iap_recalc_purlot2`
select `lot_item_code`, `lot_cost`,  
if(isnull(`salelot_item_code`), `lot_count`, (`lot_count`-`salelot_quantity`)) AS lot_count
, `lot_date`
from  `iap_recalc_purlot`

left join `iap_recalc_applot_sum` 
    on YEAR(`sale_ymd`) = 2014
    AND UPPER(`salelot_item_code`) = UPPER(`lot_item_code`)
    AND `sale_ymd` = `lot_date`
    AND `salelot_lot_cost` = `lot_cost`

group by `lot_item_code`, `lot_cost`, `lot_date`;

TRUNCATE `iap_recalc_purlot`;

insert into `iap_recalc_purlot`
SELECT * FROM iap_recalc_purlot2
WHERE `lot_count` > 0;

SELECT COUNT(*), SUM(`lot_count`), SUM(`lot_cost` * `lot_count`) 
FROM `iap_recalc_purlot`;

------------------------------------------------------




Bad PurLot Date to fix
SELECT `iap_purchase_lots`.*, `pur_date`, pur_order, `purdet_from_set`, `purdet_quantity`, `purdet_cost`
FROM `iap_purchase_lots` 
join iap_purchases on pur_order = `lot_po`
join iap_purchase_detail on purdet_purid = pur_id and purdet_item = `lot_item_code`
WHERE year(`lot_date`) > 2019


Sales
SELECT year(sale_date) as Year, COUNT(*) as Records, sum(`saledet_quantity`) as ItemsSold, sum(`saledet_total_cost`) as RecordedCost, sum(`saledet_total_price`) as RecordedPrice, sum(`saledet_total_profit`) as RecordedProfit, 
sum(prc_cost * `saledet_quantity`) as BookCost, sum(prc_price * `saledet_quantity`) as BookPrice

from iap_sales_detail

join iap_sales on sale_id = `saledet_sid`
join iap_supplier_prices on prc_item_code = `saledet_item_code`
                        and prc_effective_until = "2099-12-31"

where sale_type <> "W"

GROUP BY year(sale_date)


