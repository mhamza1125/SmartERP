<?php

namespace App\Support;

use Illuminate\Support\Collection;

/**
 * What each permission means, for the Roles & Permissions screen, and the one dependency rule
 * applied whenever a role is saved. Permission names stay "{module}_{action}" in the
 * permissions table; the policies (app/Policies) check them.
 *
 * Dependency rule (normalize()): any permission of a module also grants that module's Access
 * (list + menu), and Edit also grants View. Without it a role could e.g. create orders but not
 * open the Orders list, or edit a record it cannot open — the "permission denied although it is
 * assigned" errors.
 */
final class PermissionCatalog
{
    public const ACTIONS = [
        'access' => ['Access', 'Open the module: menu entry and list'],
        'show' => ['View', 'Open records; print and export them (PDF prints, Excel invoices)'],
        'create' => ['Create', 'Add new records'],
        'edit' => ['Edit', 'Change records, including status changes'],
        'delete' => ['Delete', 'Delete records (refused while other records depend on them)'],
    ];

    /**
     * section => [module => [label, what it covers]]
     */
    public const SECTIONS = [
        'Sales' => [
            'orders' => ['Orders', 'Orders, order status, order / production / proforma invoice prints, proforma Excel export'],
            'deliveries' => ['Deliveries', 'Deliveries, packing lists, delivery returns, commercial invoice (print and Excel export)'],
            'customers' => ['Customers', 'Customer records. The customer ledger also needs Transactions: View'],
        ],
        'Production & Stock' => [
            'issuances' => ['PTC & Issuances', 'PTCs (issue, receive, release, close), issuances and their receivings, machine material issuance, issuance groups'],
            'stocks' => ['Inventory Stock', 'Product / material / machine stock pages and stock batches (Edit = reserve or split batches)'],
            'machines' => ['Machines', 'Machine records'],
        ],
        'Purchasing' => [
            'purchases' => ['Purchases', 'Material and product purchase orders'],
            'receives' => ['Purchase Receivings', 'Receiving purchased goods (inspection / approved quantity)'],
            'returns' => ['Purchase Returns', 'Returning received goods to the vendor'],
            'vendors' => ['Vendors', 'Vendor records. The vendor ledger also needs Transactions: View'],
            'contractors' => ['Contractors', 'Contractor records. The contractor ledger also needs Transactions: View'],
        ],
        'Products & Materials' => [
            'products' => ['Products', 'Products and sizes; Edit includes bill of materials and costing / wages'],
            'materials' => ['Materials', 'Materials and material processing'],
        ],
        'Finance' => [
            'transactions' => ['Transactions', 'Payments, receipts, expenses, journal vouchers (with expense head), transfers, balance adjustments, cash book, work wages'],
            'banks' => ['Bank Accounts', 'Company bank / cash accounts and bank ledgers'],
            'assets' => ['Assets', 'Asset register'],
        ],
        'HR & Payroll' => [
            'employees' => ['Employees', 'Employee records. The employee ledger also needs Transactions: View'],
            'attendance' => ['Attendance', 'Attendance from the machine, salary calculation, work hours, non-working days'],
            'payroll' => ['Monthly Payroll', 'Payroll sheets, salary slips and payroll payment vouchers (saving posts the salary transactions)'],
        ],
        'Reports & Settings' => [
            'reports' => ['Reports', 'Reports menu: daily issuance / receiving, material and purchase ledgers, product stock reports'],
            'settings' => ['Company Settings', 'Company details, payroll defaults, attendance machine database location'],
            'heads' => ['Heads', 'Lookup lists (sizes, stages, units, expense heads...). Company Settings permissions also allow this'],
            'categories' => ['Categories', 'Product categories. Company Settings permissions also allow this'],
        ],
        'Administration' => [
            'users' => ['Users', 'User accounts and the role each user has'],
            'roles' => ['Roles & Permissions', 'Roles and the permissions given to them'],
        ],
    ];

    public static function moduleOf(string $permission): string
    {
        return substr($permission, 0, strrpos($permission, '_'));
    }

    public static function actionOf(string $permission): string
    {
        return substr($permission, strrpos($permission, '_') + 1);
    }

    /**
     * Adds the permissions implied by the dependency rule (see class comment).
     *
     * @param  iterable<string>  $names  permission names
     * @return array<int, string>
     */
    public static function normalize(iterable $names): array
    {
        $names = collect($names)->values();
        $implied = collect();
        foreach ($names as $name) {
            $module = self::moduleOf($name);
            $action = self::actionOf($name);
            if ($action !== 'access') {
                $implied->push($module.'_access');
            }
            if ($action === 'edit') {
                $implied->push($module.'_show');
            }
        }

        return $names->merge($implied)->unique()->values()->all();
    }

    /**
     * Permissions (rows with id + name) grouped for the role screen:
     * section => [module => ['label', 'description', 'permissions' => [action => row]]].
     * Modules not in the catalogue are listed under "Other".
     */
    public static function grouped(Collection $permissions): array
    {
        $byModule = $permissions->groupBy(fn ($p) => self::moduleOf($p->name));
        $groups = [];
        foreach (self::SECTIONS as $section => $modules) {
            foreach ($modules as $module => [$label, $description]) {
                if ($byModule->has($module)) {
                    $groups[$section][$module] = ['label' => $label, 'description' => $description, 'permissions' => self::byAction($byModule->pull($module))];
                }
            }
        }
        foreach ($byModule as $module => $rows) {
            $groups['Other'][$module] = ['label' => ucwords(str_replace('_', ' ', $module)), 'description' => '', 'permissions' => self::byAction($rows)];
        }

        return $groups;
    }

    private static function byAction(Collection $rows): array
    {
        $sorted = [];
        foreach (array_keys(self::ACTIONS) as $action) {
            if ($row = $rows->first(fn ($p) => self::actionOf($p->name) === $action)) {
                $sorted[$action] = $row;
            }
        }
        foreach ($rows as $row) {
            $sorted[self::actionOf($row->name)] ??= $row;
        }

        return $sorted;
    }
}
