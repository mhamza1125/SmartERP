===============================================================================================
===============================================================================================

Optional. 
1. Material processing. We can have a table view that what is outgoing and what is incomming
2. Make every product select dropdown to have same data i.e. article no, name and size if available
3. Each report have it's own permission

===============================================================================================

Some more work

1. The products have the labor working wages. As general wage and employee / contractor specific wage. Which is fine. But we need to see the person specific wage and it's history on their info page as well. like if employee / contractor have different working wage than the general wage it will be shown to it's employeeInfo and contractorInfo page as well. Also keep the track of the wage updates of these person so when they demand another raise we know when and how much we raised last time.

2. This task is about Purchase / Order. Will also be linked to customer and vendors
2.1. For purchases We need to add the product / material prices with respect to vendor in product page
Like we buy same product on differnet price from different vendors. And simlarly when we buy the product / material on differnt price it's only price is updated. Now when brought on higher / updated price it's vendor respective price would be updated. And we can see the price update history as we are seeing now on the material page. 
2.2. Same goes for selling we will Add these prices to customers that XYZ customer is buying that product on that price. And when sold higher that price should be updated. The history of price will be maintained the same way we're seeing now. 

2.3 Addition to that when we make the purchase. We select the vendor and the product / material. Add another readable field there that will show us the price if added. Afer we select the vendor adn product / material
This is for both add / edit pages of material and product purchase.
2.4 Same for creating order. When se select customer and the product. There should also be a readonly field showing the price for that product. 
This is for both add / edit order pages. 

By this way we Don't have to go to differnet pages to see the prices on making purchase order or customer order.

Also add a table. Two tables might be better to show the updated price history of both for selling(Shown when person have the customer module permission) and buying (table shown when person has the purchse module permission)



1. Show contractor rates of on contractor pages. 
    Price update history of contractor (To be added)

2. Price list of products & materials accroding to Customers
    and Vendors in Orders and Purchases. OK

3. Rejection / Rework / Return / Stock Adjust Module. As explained

Add print in assets / liability, Voucher + Ledger

Early checkout is also late hour

New work

1. Rejection in the software

In ptc the products can be put in rejection and it's stock is also managed but we never say rejection of what stage. Like cutting rejection, rejection on printing, embossing etc. It's the stage of product when it's rejected. We should track that as well. And Allow us to add returns of purchases of the rejections as well. As we have the work style that we can regurn the products at any stage even after some work is done on it like it's rejected due to any reason at same price as we buy that. As on buy the quality check inspectio is only done lik 5%. But on delivery every product is tested properly and we usually take out the defected pieces and return them. So ultimately we can return the purchaes products even if they are processed and in some different stage on the same buy price of that purchase. 

Also allow us to adjust stock. Like genral adjustment of increase / decrease of any material / product (in any stage also rejection) stock adjustment. 


New work. 

1. Add print of assets and liabliity module. For it's whole ledger and also for the individual transactions like other in the system

2. Currently in attendence / payroll module. The late attendence in the morning consider it as late hour / minute. But early checkout dosen't count it in late hours. That is also late. For example the standard time of department is 9am to 6pm
The employee checkIn at 9:15. He's 15 minutes late. Then he checkout at 5pm and he went off 1 hour early. Total late hours would be 1hour 15minutes. Currently it only shows the late check IN i.e. 15 minuts. Update it to include the early checkout as well

3. Major task of the

======================================================================================

1. There is currently a Asset module, Also add some liability module and enhance both where possible. Allow us to select if the liability or asset will affect the money (cash or any account) or not. 

2. Pick up the customer price section from the 4th tab of productInfo and take it to the customer info page we can see there at what price is he buying products. So that price should not be in the productInfo. Keep other prices there.

3. Late time in payroll module is now calculated according to the WorkIN and WorkOut time mentioned in the departments head. So remove the generic time. So remove this http://127.0.0.1:8000/workTime and http://127.0.0.1:8000/workHoliday that is not needed. Soyou also remove the Hours per day from payroll as that is being calculated from the time in and out of the department. 

4. There is balance of vendor shown in /vendor but it doesnot match with it's own ledger on ledger view. Fix that ledger amount in the /vendor page. 

===============================================================================================


===============================================================================================

0. Multiple Proforma / Commercial Invoice (Earlier Task)

1. http://192.168.10.99:8080/ERP/public/cashBalance
Fix the filter cash amount here on this page. 

2. Invoice creation and then invoice is posted

3. Reporting
    Product purchase ledger just like material ledger
    Breakdown the journal voucher it's always teh non cash entry and also show it in expense if the expense is selected.

4. And there is no ledger of the expense

5. Editable month of the payroll. Like we're paying for october in november

6. Something about payroll, skipped

7. Don't show the inactive employees in payroll

8. Both the overtime and late minutes in hours in payroll

9. Allow any two decimals entry in overtime rate, overtime hours and other fields

10. Show the previous balance for fixed advance 

11. IN payroll. Make the following fields editable. 
    Total Days, and Hours
    Add the columns, Add leaves (Non deducted options)

12. In department heads of employee define the start and end time of that person

Taking loan from somewhere. Still to do. 

===============================================================================================

1. Fix logo in the header logo. reduce the size as earlier. and bring the page title in the center.

2. IN all bills like Order / and transactions etc will have the following text size
Amount in words: font size: 11(current 12), amount size 12 (current 13)

3. IN proforma invoice; The certified to be true and company text size is 11. increase it to 18

4. Make the header details of website, phone and email editable from /company page they are already there just use these. 

===============================================================================================

5. Default prints of table pages are working on my software but not on working. How to fix it. it's builtin table with id tableExport example is order.blade.php. Do i need to run some command?  
6. IN header cards of every page just like /purchase/print/id increate the text size of ittle lable to 10. Other text remains 11. 
7. Fix the settings access. I've given access to a new role. But on visiting /head it still says unauthorized access to that. Fix tha. 

===============================================================================================

8. Bring the sign places in the field a bit lower like 1 or two enters to have enough space for sign or stamps in every print where there is that. 
9. Show the stock of material in /material/id page it's currently shown in it's prnt. it should be also visible in it's view page. 
10. The ledger prints are fine but similarly show that details in their views as well. For all people like vendor, contractor,employee and customer. 
11. Show order description in proforma invoice, order print, commercial invoice, production order. where ever suitable. in header or after the items table. 
12. In ptc print: And make them in two colums
{{-- QA DHR completion checklist, ticked and signed by hand --}}
<div class="document-section avoid-break">
    <div class="table-title">Production Activities</div>
    <div class="qa-box">
        <div class="qa-box__title">QA Completion Checklist</div>
        <ol class="qa-checklist">
            <li><span class="qa-check"></span> 1. Process traveler fully completed</li>
            <li><span class="qa-check"></span> 2. QA Inspection Checklist</li>
            <li><span class="qa-check"></span> 3. Re-Work / Rejection entry (if applicable)</li>
            <li><span class="qa-check"></span> 4. Material test, hardness test report (if applicable)</li>
        </ol>

13. /payroll/create. The loan should be only previewable not editable. It's readonly field and wiil be taken form teh software that 
    Don't show the salary calculation formula in the print

===============================================================================================

1. Make the view page functional for BRS / Bank Cash Transfer. Currently, it shows "None" when clicking the View button in `/transactions`.

2. Fix the view of `/brs`. Check `/addTransfer` and `/addBRS`, and maintain a unified view for both.

3. Rename General Voucher to Journal Voucher everywhere, including the General Voucher page and its preview. It should be called Journal Voucher, not General Voucher.

4. Add payment voucher printing to monthly payroll. Currently, transactions and the bank ledger have payment voucher printing, but monthly payroll only has salary slip printing. Reuse the existing payment voucher printing functionality from transactions, cash book, and bank ledger.

5. Add a single print button for all payment vouchers and a single print button for all salary slips.

6. In payments to vendors and others, do not make purchase selection mandatory. Also, add a General Payment option to specify the purpose of the payment.

7. Show the employee/vendor's loan details and the deduction ratio. Loans are given through payments to employees, vendors, etc.

8. On `/addTransfer` ([http://127.0.0.1:8000/addTransfer](<http://127.0.0.1:8000/addTransfer?utm_source=chatgpt.com>)), fix the Cheque No field. When I enter text containing spaces in the `Check_no` field, the first word appears as the cheque number and the remaining words appear in the description. When editing, the entire cheque number appears in the description as "Check No: This is cheque no". Fix this issue.

9. In the People Ledger view, display details as smaller text below the main information instead of normal-sized text. Also, apply a maximum width to prevent the details from occupying too much space. Use `/transaction` ([http://127.0.0.1:8000/transaction](<http://127.0.0.1:8000/transaction?utm_source=chatgpt.com>)) as a reference.

10. Add the General Payment option, as implemented in `/addVPayment`, to Contractor (`/addCPayment`) and Employee (`/addEPayment`) payments. Also, improve the layouts of the following pages:

- `/addEPayment`: Rearrange the fields to properly occupy the empty space after the Employee Select option.
- `/addCPayment`: Use the empty `col-6` space after the "Payee Cash / Bank (if any)" field appropriately.
- `/addOPayment`: Address the empty space after the "File / Images" field.

11. On `/bank`, make the Edit modal a two-column layout for better visibility.

12. On `/addEPayment`, when a loan is selected, the Loan Type field appears in a very narrow column. Move it to a more appropriate position and improve its layout.

13. On `/payroll/create`, improve the header layout. The first row currently has too many fields and unused space after the Date field, while the second row has only one select option. Arrange the fields across two rows in a more organized layout.

14. On `/bank`, combine the two tabs for viewing the ledger and editing bank details into a single unified view. There is no need for two separate views.

15. Improve the headers and footers of all people ledgers (Employee, Vendor, Contractor, and Customer). Avoid repeating the same information and show only useful details.

- For example, on `/eLedger/1`, the balance is displayed three times: once in the header and twice in the footer. Show it only once.
- A loan repayment of 10k is incorrectly included in the "Total Wages Earned" figure. Correct this calculation.
- Remove unnecessary information from the footer. The ledger itself provides sufficient details.
- Improve the descriptions as explained above.

16. Set the font weight of all printed page titles to 900 (bold).

17. Unify the two bank pages into a single page for viewing bank details and ledger previews.

18. When clicking Pay, open the payment page with the relevant person and details already preselected.

===============================================================================================

Deletion option to the software. 
Update the Journal voucher and add the expence head. 
Add excel printing to commerial invoice and proforma invoice
Attendance file connection to salary module
(Send me that file again. Email not received)

===============================================================================================

Printing updates in the software. 

1. The footer is being removed and the header is being edited in all over the software printing
    The header will have three sections or two if you prefer. I'll prefer 3
    On left side there will be the logo 
    On right side there will be three small rows containing Phone, Email and website
    On middle there will be the print page title liek Purchase XYZ, Order ABC, etc

2. In proforma invoice the text "Proforma invoice no" should be changed to P-Invoice No and "Invoice Date" should be updated to P-Invoice Date. 
Also add the payment terms along with other details in header, Payment terms are extracted from the order of the proforma invoice. 
Columns update below the table. 
    Increase the size column size. update the text Quantity to QTY and Unit price to Price. 
    When bank is selected before generating the proforma invoice show more details about the bank in the select optoin. 
    Also the text needa to be moved to left which states "Certified to be true and correct and then compnay name. Also capatilize the company name"
    Center align the columns of size, quantity, price, total and other in the software that shows such units / pricing etc
    Bank selection is here when modal pops up. http://127.0.0.1:8000/order/53
    Here is proforma invoice url: http://127.0.0.1:8000/order/proforma/53?bank_id=1&hs_code=21&selling_type=12&uom=23
    You need to display the bank name from heads here in the modal select option
    For all columns in the software table printing same align the <th> as the <td>. Sometimes the head is center alighed and it's value in comming rows is left or right aligned. They should be same always. 

2. http://127.0.0.1:8000/order/production/53
In production order here remove the stage column thati s not needeed. And just make sure the stock displayed here is correct and matches with the /stock page. http://127.0.0.1:8000/stock

3. In purchase order print. Increase teh size of title "Purchase Items" to make it visible. 
http://127.0.0.1:8000/purchase/print/96

4. In commercial invoice of delivries. http://127.0.0.1:8000/delivery/commercial/23?hs_code=123&selling_type=123&uom=12&bank_id=1&statement_of_origin=12
Show the FI number from the settings and the Payment terms from teh order in the header along with other details. and the modal popup for commercial invoice here http://127.0.0.1:8000/delivery/23 should also show the bank name just as above task to be mroe sure what wer're selecting. 
IMP: We need to manage the frieght charges added to order amount and it should also be shown in ledger. It's like we're charging hte customerupon delivery. Like 100$ was it's order and 20$ is freight charges and customer will pay us the 20$. Manage it somewhere wether in order wether in delivery or whereever you prefer but there should be these additional charges along with order / delivery. And those charges should be showin in the commercisal invoice as well added to the total amount in appropriate way. They should correctly appear in cusotmer ledger. 

5. In packing list do not show empty columns like weight if we have not added the weight it's column and any other detail about weight should not be shown. 
http://127.0.0.1:8000/delivery/23

6. In delivery print there is no consignee. http://127.0.0.1:8000/delivery/print/23
Remove that from it' sprint and keep it simple. simplify the columns lik equantity delivered to just QTY. And the alighment should be teh same center or right but both the header and it's view cell should be aligned the smae way in all over the software. I'll prefer right aligh fur such mathematical values of quantity, price etc. 

7. http://127.0.0.1:8000/receive/print/110
There is no header of page and also unify the title of table like there are receive materials and receive products. How about you just say received items. and make it a bit more visible just like in above task #3

8. In prints of purchase / receive / return these tasks are same to make the table title better adn in all pages of software right align the mathematical columns, simplify the column titles from large to small and simple. 
And on receive print add "Received By: _______________________" after the table we'll fill it manually where needed. 

The smae goes for the material / product purchase. Tehre souldbe these improvements of table, title and add that received by
Same goes for the return page print but in return there shoulld be the two empty labels liek Issues by: __________ and received by: __________________ in same row one on left one on right. other detials are smae to add teh page title to knwo which print is it and we don't have to guess it. As I've defined the structure earlier. Logo on left. Phone, email and website on right and page title in middle.
http://127.0.0.1:8000/receive/print/110
http://127.0.0.1:8000/purchase/86
http://127.0.0.1:8000/return/51

9. In material print the stock shown here is not correct. it should match with the sotock shown in stock page. 
http://127.0.0.1:8000/stock/material. 
http://127.0.0.1:8000/material/46

10. In product cost informatin page. The table shows general cost as well as the labor specific cost and at the nd of page the total cost is sum of all which is wrong the toal cost is only for sum of general cost. 
http://127.0.0.1:8000/productCost/122
Also the category and unit is not shown here. 
Also add and show that print in the product view page so we don't have to come here to see the print. 
The print of the costing / wages should be there along with product print in dropdown. 
http://127.0.0.1:8000/product/122

===============================================================================================

11. In /ptc/id
The issuance records sequence is not good. it should be in correct sequence backwart. First issuance at last and last issuance at top. Curently it's random it's showing ptc in this order. 2, 3, 4, 1. it should have been like 4, 3, 2, 1. 

12. Some button took us to /reeiveIssue page from ptc. Which button did that. just verify and tell. 

13. in ptc /ptc/10181 Whe ncreating an issuance for xyz stage. the product stock for this "	SLE-5004-5 to 8	Nozel for funnel	20MM	100 Pieces" is shown as 100 units. But when I issue that as a product component it says onl - available error in ptc issuance. Fix that there is some stock calculation error for such component product issuance. 

14. Also we've attached multiple stages to product from Stage1 to StageLast. When first issuance it always act as Stage #1 is issued for stage 2. When we'll receive it. we'll receive item in stage 2. But we also work with raw material when raw material is issues ti still treats it the same way. But we've issued teh raw materila fro stage 1. How to do it. Will you fix something ro will you add another step in product steps named "Raw Material" So when generting from raw material we'll select this as stage 1 to last and in other case we'll sleect the Stage #2 (which is currently actulal stage one with no raw material stage) to last? tell me about it. in /ptc. 

===============================================================================================

15. The normal built in table export prints are not that good. Fix them. 
Make the logo appear according to their base url they are running software on such url: http://192.168.10.99:8080/ERP/public/dashboard. and logo is not appreaing fix the path so logo appear correctly. 
Also there is issue with many prints like 
Order in /order For status column it prints and shows all the statuses available not the current status. 
Expense adn other paymetn table views. 
http://127.0.0.1:8000/expense
http://127.0.0.1:8000/ePayment
http://127.0.0.1:8000/vPayment
http://127.0.0.1:8000/cPayment
http://127.0.0.1:8000/oPayment
The colums dont' provide enough information to knwo what's the payment about when printed because we can't click on transaction to view that. So show more details on columsn where possible not in all the prints i mentioned. The expense definitaly needs soem work The description column is not enough. 
Same goes for this: http://127.0.0.1:8000/transaction
Make it's print more meaningfull to knwo just by print what the transaction was about. 
Also there is no column header in builtin table exports. There should be the column headers <th>

16. Just mentioneing the payment vouchers must be of half page. As teh footer is moved in header it's fine but it lacks some details. It shoudl show all the details as in teh view page of that transaction like the cheque no if any etc. And similarly make the debit / credit column headers right aligh to match it's column cells. 
Add print button in BAnk/Cash Transfer Info. 
http://127.0.0.1:8000/transfer/236
All details should be on the voucher like bank / cheque no, desciption etc. and fix column alignment.

17. The people ledgers should show more details. While viewing on software we can click and see the details but on prnt the description lacks details like it just list the purchase no or just text transaction etc. Make it more useful. 
http://127.0.0.1:8000/cLedger/16
http://127.0.0.1:8000/eLedger/44
http://127.0.0.1:8000/vLedger/50
http://127.0.0.1:8000/vLedger/16

===============================================================================================

19. http://127.0.0.1:8000/ptc/10173
The issuance record table is messed up with text and large header titles. Make it better Make it compact and update the page titels and use something smaller You can have small badges Use smaller header texts for these. Product Qty Issued	Product Qty Received and any other possible. Enhance the look of this page. 

20. http://127.0.0.1:8000/purchase/
Here we're using badge in purchaseType column for material and product. Use alternate colors so we can know which row is product and which is material easily. Use similar badge color currently it's black you can use gery or something other color for that. 

21. In purchase print: http://127.0.0.1:8000/purchase/print/86
Would you prefer merging the table of purchase item and receive items .It's generally teh same with some received qty column. 
Purchase Items
Sr.	Article No	Product Name	Size	QTY	Price	Total
1	SLE-1020-1	Spatula Plastic Handle	160 (40x4) MM	100.00	10.00	1,000.00

Receive Summary
Sr.	Article No	Product Name	Size	Ordered	Received	Returned	Balance
1	SLE-1020-1	Spatula Plastic Handle	160 (40x4) MM	100	100	0	0

Also if merged we don't need these "Receive Records
R1-P2611003 — 07-11-2026
R2-P2611003 — 07-11-2026" in the footer. 
You can move these in to the right side of vendor detail section 
VENDOR
V0003 — Ashraf Spatula Maker
Address of Liquid Vendor
03006134452

Also after that in each purchase print I need some static text
Here is it: 

Note: 
1. Submit the Purchase order form with bill.
2. Order brought after delivery period will be up to company to accept or not. 
3. Submit the Sample/Drawing at teh time of delivery of respective item. 
4. Quantity above ordered Qty will be up to the company to accept or not.
5. Pyment will be made on completion of teh respective purchase order unless otherwise settled.
This will be on left side 
And on right side the Urdu version of this text. 
You can divide the row in two columns english can have upto 70% space and urder can have 30%. You can update the ratio of needed
After that Three signs in same row
Prepared BY:_________  Approved By:______________  Supplier Sign:__________________

After that GateInward Pass (It's a table. Always empty)
The columns are
Date, Description, Qty Received, Batch No, Qty Balance, Qty Rejected/Return, Receiver Sign
You can have same number of rows as ordered items here and minimum of 3 rows. 

===============================================================================================

22. http://127.0.0.1:8000/ptc/10173/print
The ptc print shoudl always states all the production stages even if not worked on. You can keep the columns empty but always show all the stages from stage 1 to stage last. And fill the respective one with available data and leave other empty. 
Hide this section for now Don't remove it. YOu can just comment out that: Activity Summary
Total Activities:
9
Issuances:
4
Receivings:
4
Releases to Stock:
1
And I need another section after teh ptc table. "Production Activities"
 
It is of checklist named as "QA DHR Completion Checklist"
It will have following 4 points: 
1. Process traveler fully completed and check box
2. QA Inspection Checklist, and check box 
3. Re-Work / Rejection entry (if applicable) and check box
4. Material test, hardness test report (if applicable) and check box
Static text below it. Ponts on this checklist have been revied by qa and have been found to be completed and have been corrected to assure all the necessary information about the above noted lt has been incluced. 
Two texual spaces
QA manager ___________ and Date: _____________

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