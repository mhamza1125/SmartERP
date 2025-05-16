**Project Documentation**  

## Attendance System  

- MDB File Path: `resources/att2000.mdb`  
	{{ URL::asset('resources/att2000.mdb') }}
- Inner File Name: `CHECKINOUT`  

## System Requirements  

- PHP Version: 8.2.0  
- XAMPP Control Panel: 3.3.0  

## Database Guide  

### Table: stocks  

#### Stock ID Meaning  
- 0 - Material Processing (Stock Out)  
- 1 - Opening Stock (Stock In)  

#### Stock Status Codes  
- 0 - Not Received  
- 1 - Completely Received  
- 2 - Partially Received  
- 3 - Delivery  
- 4 - Machine Material  
- 5 - Material Processing  

#### Default Stock Entries (stock_items in stocks table)  
1. Material Processing  
2. Opening Stock  

#### Example Insert Query  
```
INSERT INTO stocks (stock_id, issue_id, issue_for, stock_no, order_id, machine_id, table_name, employee_id, stock_type, stock_date, stock_status, description, created_by, created_at, updated_at) VALUES
(0, NULL, 0, 'I24000000', 0, NULL, 'mprocess', 0, 2, '2024-05-09', 0, NULL, 1, '2024-04-26 14:05:16', '2024-05-09 08:39:30'),
(1, NULL, 0, 'I23000000', 0, NULL, 'openingStock', 0, 1, '2024-05-09', 0, NULL, 1, '2024-04-26 14:05:16', '2024-05-09 08:39:30');
```  

## Activity Logging  

This project uses the Spatie Activity Log package for tracking changes.  

Documentation: https://spatie.be/docs/laravel-activitylog/v4/installation-and-setup  


===================================================================================================
===================================================================================================
===================================================================================================

1. Surgical: Showing all employees in Machiene Material Issuance
	While Other is showing just the wages employees
		StockController
			create2() and edit2($id) Methods
	        $employee = $this->employeeRepository->all();
	        $employee = $this->employeeRepository->wages();

2. Surgical: It is showing employee designation in Employee.blade
	While other isn't showing that, Just a 'designation' column

3. Add countries / cities form API

