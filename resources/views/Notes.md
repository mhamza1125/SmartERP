===============================================================================================
===============================================================================================

1. http://192.168.10.99:8080/ERP/public/order/45
    http://192.168.10.99:8080/ERP/public/order
Allow us to change the status of orders. Skipped

2. /delivery/{id}
Update it's print label from Delivery Chalan to valid text

3. Fix date format in all over the software in 
    Prouction order print
        Production order print
        Remove the text "Production items" in production order print
4. Fix header logo
And fix the footer at the bottom

5. Purchase order print. Fix and display the Material / products accordingly. With sizes and details

6. Printing width of all prints should be same

7. REceive products / material print. Fix width and details showns and remove the table header

8. Align print cells to the left not in the middle. ONly the product / material name column not all. And amounts / qty in center

9. Order information print: There is no need of transaction number / voucher number

10. No need of pending and rejection in purchase of material and product. Only approval and add up to the stock

===============================================================================================

1. Payroll should show the same outstanding balance as in the person's ledger, Including things like OpeningBalance, GeneralVoucher etc

2. http://127.0.0.1:8000/order/53
Show recent deliveries of the order along with It

3. http://127.0.0.1:8000/delivery-return/5
Add the button of delivery return, As it is already fixed 

===============================================================================================

1. http://127.0.0.1:8000/order/production/53
Sizes are always grouped together. That should not be like this fix that. 

===============================================================================================

Todo: 
1. Fix migration of update material and product id columns in vendors table
2. Fix purchase print
3. Fix js ledger fetching for Order
4. Don't receive extra product in issuance / receiving (Can receive more when material is issued)
5. Fix description view in payments. Show it with our print row in table
6. Order is always the last stage of product

Todo (Optional):

1. Balance Sheet (Combined ledger values)
2. Hide customer detials on accounts (Permissions)
3. Profit/Loss statements
4. Customer amount display fix Net / Gross amount
    (Customer fully paid, But we received little less due to bank charges)
5. Remove bqty from the products and respective pages, Not needed
6. Use the product stages as costing heads
7. Issuance group needs rework Not showing up the products
8. Single payment of multiple orders
9. We should pay vendor regardless of purchase
10. Delivery return affected nothing
    That should affect stock as well as customer ledger
11. Default Stock of Material / Product Adjustment (Increase / Decrease)

===============================================================================================
=====================================================================================================================
================================================= Work to do ========================================================
=====================================================================================================================
I've commented out FireBird and OCI extension in PHP Production, Development & Configuration =====================================================================================================================

1. Product component stage is not being defined

2. Material / Product purchase the name is not shown (purchase/97)

3. In material ledger report the stockIn / StockOut needs to be fixed
https://erp.agilewebsolutions.net/public/materialDetail

4. Here order qty needs to be fixed (It is showing the actual received after return)
https://erp.agilewebsolutions.net/public/purchase/95

5. Size not appraring on addIssuance product dropdown when order is selected

6. SQLSTATE[23000]: Integrity constraint violation: 1062 Duplicate entry '001' for key 'issue_no'
Error on creating PTC Use some better approach for issue_no as it is being used to fetch something in deliveries

7. Delivery return is not affecting the ledger

8. How pricing is being managed in multi-order delivery?

=====================================================================================================================
=====================================================================================================================

Peach tree
Oracle Software
Surgicraft software
Impulse Application Software

=====================================================================================================================
=====================================================================================================================

Security of Project
Use mac address check in project
Mac Address is placed in DB & time calculation starts
System crashes after 3 months of deployment

===============================================================================================
===============================================================================================