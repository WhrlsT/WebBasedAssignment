<?php
/**
 * SimplePager - A simple pagination class
 */
class SimplePager {
    public $page;
    public $page_count;
    public $item_count;
    public $items_per_page;
    public $result;
    
    /**
     * Constructor
     * 
     * @param string $query SQL query
     * @param array $params Query parameters
     * @param int $items_per_page Number of items per page
     * @param int $page Current page number
     */
    public function __construct($query, $params = [], $items_per_page = 10, $page = 1) {
        global $_db;
        
        $this->items_per_page = $items_per_page;
        $this->page = max(1, intval($page));
        
        // Count total items
        if (stripos($query, 'SELECT COUNT') === 0) {
            // Query already returns count
            $stmt = $_db->prepare($query);
            $stmt->execute($params);
            $this->item_count = $stmt->fetchColumn();
        } else {
            // Extract the FROM part and create a count query
            $from_pos = stripos($query, 'FROM');
            if ($from_pos !== false) {
                // Get the part of the query after FROM
                $from_clause = substr($query, $from_pos);
                
                // Remove any ORDER BY clause for the count query
                $order_pos = stripos($from_clause, 'ORDER BY');
                if ($order_pos !== false) {
                    $from_clause = substr($from_clause, 0, $order_pos);
                }
                
                // Remove any GROUP BY clause for the count query
                $group_pos = stripos($from_clause, 'GROUP BY');
                if ($group_pos !== false) {
                    $from_clause = substr($from_clause, 0, $group_pos);
                }
                
                // Create a proper count query
                $count_query = "SELECT COUNT(*) " . $from_clause;
                
                try {
                    $stmt = $_db->prepare($count_query);
                    $stmt->execute($params);
                    $this->item_count = $stmt->fetchColumn();
                } catch (PDOException $e) {
                    // If the automatic count query fails, try a simpler approach
                    // This is a fallback for complex queries with subqueries or JOINs
                    $temp_query = preg_replace('/SELECT\s+.+?\s+FROM/is', 'SELECT COUNT(*) FROM', $query);
                    $temp_query = preg_replace('/ORDER\s+BY\s+.+$/is', '', $temp_query);
                    
                    try {
                        $stmt = $_db->prepare($temp_query);
                        $stmt->execute($params);
                        $this->item_count = $stmt->fetchColumn();
                    } catch (PDOException $e2) {
                        // If all else fails, execute the original query and count the results
                        $stmt = $_db->prepare($query);
                        $stmt->execute($params);
                        $all_results = $stmt->fetchAll(PDO::FETCH_ASSOC);
                        $this->item_count = count($all_results);
                    }
                }
            } else {
                throw new Exception("Cannot determine count from query");
            }
        }
        
        // Calculate total pages
        $this->page_count = ceil($this->item_count / $this->items_per_page);
        
        // Adjust current page if it's out of bounds
        if ($this->page > $this->page_count && $this->page_count > 0) {
            $this->page = $this->page_count;
        }
        
        // Get the results for the current page
        $offset = ($this->page - 1) * $this->items_per_page;
        
        // Add LIMIT clause if not already present
        if (stripos($query, 'LIMIT') === false) {
            $query .= " LIMIT " . $this->items_per_page . " OFFSET " . $offset;
        }
        
        $stmt = $_db->prepare($query);
        $stmt->execute($params);
        $this->result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Outputs HTML for pagination links
     * 
     * @param string $queryString Additional query string parameters
     * @param string $containerClass CSS class for the container
     * @param array $options Additional options for customization
     */
    public function html($queryString = '', $containerClass = '', $options = []) {
        if ($this->page_count <= 1) {
            return; // Don't show pagination if only one page
        }
        
        // Default options
        $defaults = [
            'prev_text' => '&laquo; Previous',
            'next_text' => 'Next &raquo;',
            'prev_class' => 'pager-prev',
            'next_class' => 'pager-next',
            'active_class' => 'active',
            'ellipsis_text' => '...'
        ];
        
        // Merge with user options
        $opts = array_merge($defaults, $options);
        
        // Start container
        echo '<div ' . $containerClass . '>';
        
        // Previous button
        if ($this->page > 1) {
            echo '<a href="?' . $this->addPageParam($queryString, $this->page - 1) . '" class="' . $opts['prev_class'] . '">' . $opts['prev_text'] . '</a>';
        }
        
        // Calculate range of pages to show
        $startPage = max(1, $this->page - 2);
        $endPage = min($this->page_count, $this->page + 2);
        
        // Adjust start and end to always show 5 pages if possible
        if ($endPage - $startPage + 1 < 5 && $this->page_count >= 5) {
            if ($startPage == 1) {
                $endPage = min($this->page_count, 5);
            } elseif ($endPage == $this->page_count) {
                $startPage = max(1, $this->page_count - 4);
            }
        }
        
        // First page + ellipsis if needed
        if ($startPage > 1) {
            echo '<a href="?' . $this->addPageParam($queryString, 1) . '">1</a>';
            if ($startPage > 2) {
                echo '<span class="pager-ellipsis">' . $opts['ellipsis_text'] . '</span>';
            }
        }
        
        // Page numbers
        for ($i = $startPage; $i <= $endPage; $i++) {
            $class = ($i == $this->page) ? ' class="' . $opts['active_class'] . '"' : '';
            echo '<a href="?' . $this->addPageParam($queryString, $i) . '"' . $class . '>' . $i . '</a>';
        }
        
        // Last page + ellipsis if needed
        if ($endPage < $this->page_count) {
            if ($endPage < $this->page_count - 1) {
                echo '<span class="pager-ellipsis">' . $opts['ellipsis_text'] . '</span>';
            }
            echo '<a href="?' . $this->addPageParam($queryString, $this->page_count) . '">' . $this->page_count . '</a>';
        }
        
        // Next button
        if ($this->page < $this->page_count) {
            echo '<a href="?' . $this->addPageParam($queryString, $this->page + 1) . '" class="' . $opts['next_class'] . '">' . $opts['next_text'] . '</a>';
        }
        
        // End container
        echo '</div>';
    }
    
    /**
     * Helper function to add page parameter to query string
     */
    private function addPageParam($queryString, $pageNum) {
        if (empty($queryString)) {
            return 'page=' . $pageNum;
        }
        
        // Remove existing page parameter if it exists
        if (strpos($queryString, 'page=') !== false) {
            $queryString = preg_replace('/([&?])page=[^&]*(&|$)/', '$1', $queryString);
            $queryString = rtrim($queryString, '&?');
        }
        
        return $queryString . (strpos($queryString, '?') !== false ? '&' : '') . 'page=' . $pageNum;
    }
}