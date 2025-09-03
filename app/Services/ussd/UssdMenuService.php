<?php
namespace App\Services\ussd;

class UssdMenuService
{
    private int $pageSize = 3;

    public function paginate(array $items, int $page = 1): array
    {
        $start = ($page - 1) * $this->pageSize;
        $pageItems = array_slice($items, $start, $this->pageSize);
        return [$pageItems, $page];
    }

    public function buildMenu(array $items, string $type, string $lang, int $page, array $messages): string
    {
        [$pageItems, $page] = $this->paginate($items, $page);

        $response = "CON " . $messages['select_' . $type] . "\n";
        foreach ($pageItems as $i => $item) {
            $response .= ($i + 1) . ". " . $item['name'] . "\n";
        }

        if (($page * $this->pageSize) < count($items)) {
            $response .= "N. " . $messages['next'] . "\n";
        }

        $response .= "0. " . $messages['back'] . "\n";
        $response .= "00. Main Menu";

        return $response;
    }
}
