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
     * Uses the structured company fields; any that are empty fall back to the
     * labelled values in footer_text ("address <br> Phone: x || Email: y || Web: z").
     */
    protected function buildContacts(): array
    {
        $contacts = ['phone' => null, 'email' => null, 'website' => null];

        if (! $this->company) {
            return $contacts;
        }

        $fromFooter = [];
        $items = preg_split('/\s*\|{1,2}\s*|<br\s*\/?>|\R/i', (string) $this->company->footer_text);
        foreach ($items as $item) {
            $item = trim(strip_tags($item));
            if (preg_match('/^(phone|tel|cell|mobile)\s*:\s*(.+)$/i', $item, $m)) {
                $fromFooter['phone'] ??= trim($m[2]);
            } elseif (preg_match('/^(email|e-mail)\s*:\s*(.+)$/i', $item, $m)) {
                $fromFooter['email'] ??= trim($m[2]);
            } elseif (preg_match('/^(web|website)\s*:\s*(.+)$/i', $item, $m)) {
                $fromFooter['website'] ??= trim($m[2]);
            }
        }

        foreach (array_keys($contacts) as $key) {
            $value = trim((string) $this->company->{$key});
            // A placeholder such as "." or "-" counts as empty
            $contacts[$key] = preg_match('/[\p{L}\p{N}]/u', $value) ? $value : ($fromFooter[$key] ?? null);
        }

        return $contacts;
    }
}
