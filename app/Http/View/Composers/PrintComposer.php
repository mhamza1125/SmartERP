<?php

namespace App\Http\View\Composers;

use App\Models\Company;
use Illuminate\View\View;

class PrintComposer
{
    protected ?Company $company;

    protected array $contacts;

    public function __construct()
    {
        $this->company = Company::first();
        $this->contacts = $this->buildContacts();
    }

    public function compose(View $view): void
    {
        $view->with('company', $this->company);
        $view->with('printContacts', $this->contacts);
    }

    /**
     * For the main app layout: what the in-page JavaScript prints (DataTables print,
     * deliveryInfo / print.js) need, with URLs built from the request's own base URL.
     */
    public function layout(View $view): void
    {
        $view->with('printConfig', [
            'baseUrl' => url('/'),
            'logo' => printLogoUrl($this->company),
            'companyName' => $this->company->name ?? '',
            'contacts' => $this->contacts,
        ]);
    }

    /**
     * Phone, email and website shown on the right of the print header.
     *
     * Taken straight from the Phone, Email and Website fields on the /company page,
     * so whatever is saved there is what prints. An empty field (or a placeholder
     * such as "." or "-") hides that line.
     */
    protected function buildContacts(): array
    {
        $contacts = ['phone' => null, 'email' => null, 'website' => null];

        if (! $this->company) {
            return $contacts;
        }

        foreach (array_keys($contacts) as $key) {
            $value = trim((string) $this->company->{$key});
            $contacts[$key] = preg_match('/[\p{L}\p{N}]/u', $value) ? $value : null;
        }

        return $contacts;
    }
}
