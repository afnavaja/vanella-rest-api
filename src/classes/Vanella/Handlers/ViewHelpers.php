<?php

namespace Vanella\Handlers;

use Vanella\Core\Url;

class ViewHelpers
{

    /**
     * Returns a list ul/ol li html list
     *
     * @param array $arr
     * @param string $type
     *
     * @return string
     */
    public static function htmlList($arr = [], $type = 'ul')
    {
        $data = null;
        if (!empty($arr)) {
            $data .= "<{$type}>";
            foreach ($arr as $item) {
                $data .= "<li>{$item}</li>";
            }
            $data .= "</{$type}>";
        }

        return $data;
    }

    /**
     * Renders a default pagination
     *
     * @param array $headers
     * @param array $records
     * @param int $defaultLimit
     * @param int $totalItemCount
     * @param string $page
     * @param boolean $isAction
     * @param string $defaultIdName
     *
     * @return string
     */
    public static function pagination($headers = [], $records = [], $defaultLimit, $totalItemCount, $page, $isAction = false, $defaultIdName = 'id')
    {
        $url = new Url();
        $limit = isset($_GET['limit']) ? intval($_GET['limit']) : $defaultLimit;
        $pageNumber = isset($_GET['page']) ? intval($_GET['page']) : 1;
        $totalPages = ceil($totalItemCount / $limit);

        $basePaginationUrl = $url->baseUrl() . $url->segment(1) . '/' . $page . '?';
        $previousPage = $basePaginationUrl . 'limit=' . $limit . '&page=' . intval($pageNumber <= 1 ? 1 : $pageNumber - 1);
        $nextPage = $basePaginationUrl . 'limit=' . $limit . '&page=' . intval($pageNumber >= $totalPages ? $totalPages : $pageNumber + 1);

        $data = ' <table class="table" style="width: 100%;" ><thead><tr>';
        foreach ($headers as $key => $value) {
            $data .= '<th scope="col">' . $value . '</th>';
        }
        $data .= $isAction ? '<th scope="col">Actions</th>' : '';
        $data .= '</tr></thead><tbody>';
        if (!empty($records)) {
            foreach ($records as $item) {
                $data .= '<tr>';
                foreach ($headers as $key => $value) {
                    $data .= '<td>' . $item[$key] . '</td>';
                }
                if ($isAction) {
                    $data .= '<td width="5%">';
                    $data .= '<a href="' . $basePaginationUrl . 'action=info&id=' . $item[$defaultIdName] . '">Info</a>';
                    $data .= '</td>';
                }

                $data .= '</tr>';
            }
        } else {
            $data .= '<tr><td colspan="' . count($headers) . '">No Results Found</td></tr>';
        }

        $data .= '
                    </tbody>
                    </table>';

        $data .= '<nav aria-label="Page navigation example">
                    <ul class="pagination">
                    <li class="page-item"><a class="page-link" href="' . $previousPage . '">Previous</a></li>';

        // Store the page number links in an array
        $pageNumberArray = [];
        for ($ctr = 1; $ctr <= $totalPages; $ctr++) {
            $isActive = $ctr === intval($pageNumber) ? ' active' : null;
            $pageNumberArray[] = '<li class="page-item' . $isActive . '"><a class="page-link" href="' . $basePaginationUrl . 'limit=' . $limit . '&page=' . $ctr . '">' . $ctr . '</a></li>';
        }

        // This one handles the page number limiter in the view
        $startPage = $pageNumber - 3;
        $endPage = $pageNumber + 2;
        foreach ($pageNumberArray as $key => $item) {
            if ($key >= $startPage && $key <= $endPage) {
                $data .= $item;
            }
        }

        $data .= '<li class="page-item"><a class="page-link" href="' . $nextPage . '">Next</a></li>
                    </ul>
                </nav>';

        return $data;
    }
}
