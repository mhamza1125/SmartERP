<?php

namespace App\Http\View\Composers;

use App\Models\Company;
use Illuminate\View\View;

class PrintComposer
{
    protected ?Company $company;

    protected array $footer;

    public function __construct()
    {
        $this->company = Company::first();
        $this->footer  = $this->buildFooter();
    }

    public function compose(View $view): void
    {
        $view->with('company', $this->company);
        $view->with('printFooter', $this->footer);
    }

    /**
     * Split company footer text into an address line and a list of contact items.
     *
     * footer_text is stored as "address <br> Phone: x || Email: y || Web: z";
     * labels are stripped so the footer shows the bare values.
     * Falls back to the structured company fields when footer_text is empty.
     */
    protected function buildFooter(): array
    {
        $footer = ['address' => null, 'contacts' => []];

        if (! $this->company) {
            return $footer;
        }

        $lines = collect(preg_split('/<br\s*\/?>|\R/i', (string) $this->company->footer_text))
            ->map(fn ($line) => trim(strip_tags($line)))
            ->filter()
            ->values();

        if ($lines->isEmpty()) {
            return [
                'address'  => $this->company->address ? trim(strtok($this->company->address, "\r\n")) : null,
                'contacts' => array_values(array_filter([
                    $this->company->phone,
                    $this->company->email,
                    $this->company->website,
                ])),
            ];
        }

        $footer['address'] = $lines->shift();

        $footer['contacts'] = collect(preg_split('/\s*\|{1,2}\s*/', $lines->implode(' || ')))
            ->map(fn ($item) => trim(preg_replace('/^(phone|tel|cell|mobile|email|e-mail|web|website)\s*:\s*/i', '', trim($item))))
            ->filter()
            ->values()
            ->all();

        return $footer;
    }
}
