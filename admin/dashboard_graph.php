<?php
// dashboard_graphs.php - Contains the data and functions for dashboard graphs

// Function to generate dummy sales data 
function generateDummySalesData($period = 'weekly') {
    $data = [];
    
    switch($period) {
        case 'weekly':
            $labels = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
            $count = 7;
            break;
        case 'monthly':
            $labels = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
            $count = 12;
            break;
        case 'yearly':
            $currentYear = date('Y');
            $labels = [];
            for ($i = 0; $i < 5; $i++) {
                $labels[] = ($currentYear - 4 + $i);
            }
            $count = 5;
            break;
    }
    
    // Generate random sales numbers
    $sales = [];
    for ($i = 0; $i < $count; $i++) {
        $sales[] = rand(5, 100);
    }
    
    return [
        'labels' => $labels,
        'sales' => $sales
    ];
}

// Function to generate dummy income data
function generateDummyIncomeData($period = 'weekly') {
    $data = generateDummySalesData($period);
    
    // Convert sales to income (multiply by average book price)
    $income = [];
    foreach ($data['sales'] as $sale) {
        $income[] = $sale * rand(15, 30);  // Average book price between $15-30
    }
    
    return [
        'labels' => $data['labels'],
        'income' => $income
    ];
}

// Generate all the data for our graphs
$weekly_sales = generateDummySalesData('weekly');
$monthly_sales = generateDummySalesData('monthly');
$yearly_sales = generateDummySalesData('yearly');

$weekly_income = generateDummyIncomeData('weekly');
$monthly_income = generateDummyIncomeData('monthly');
$yearly_income = generateDummyIncomeData('yearly');

// Convert the data to JSON for use in JavaScript
$sales_data = [
    'weekly' => [
        'labels' => $weekly_sales['labels'],
        'data' => $weekly_sales['sales']
    ],
    'monthly' => [
        'labels' => $monthly_sales['labels'],
        'data' => $monthly_sales['sales']
    ],
    'yearly' => [
        'labels' => $yearly_sales['labels'],
        'data' => $yearly_sales['sales']
    ]
];

$income_data = [
    'weekly' => [
        'labels' => $weekly_income['labels'],
        'data' => $weekly_income['income']
    ],
    'monthly' => [
        'labels' => $monthly_income['labels'],
        'data' => $monthly_income['income']
    ],
    'yearly' => [
        'labels' => $yearly_income['labels'],
        'data' => $yearly_income['income']
    ]
];

$sales_json = json_encode($sales_data);
$income_json = json_encode($income_data);
?>