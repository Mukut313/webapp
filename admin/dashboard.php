<?php
session_start();
include('../config/db.php');
include('dashboard_graph.php'); // Include our new graphs file

// Check if the connection was successful (optional, but good practice)
if (!isset($conn) || $conn->connect_error) {
    die("Database connection failed: " . (isset($conn) ? $conn->connect_error : "db.php file might have errors."));
}
// Get total books count
$sql_total_books = "SELECT COUNT(*) as total FROM books";
$total_books_result = $conn->query($sql_total_books);
$total_books = $total_books_result->fetch_assoc()['total'];
// Get total authors count
$sql_total_authors = "SELECT COUNT(DISTINCT author_id) as total FROM books";
$total_authors_result = $conn->query($sql_total_authors);
$total_authors = $total_authors_result->fetch_assoc()['total'];
// Get book counts by category
$sql_categories = "SELECT c.category_id, c.name, c.description, COUNT(b.book_id) as book_count
                   FROM categories c
                   LEFT JOIN books b ON c.category_id = b.category_id
                   GROUP BY c.category_id
                   ORDER BY c.name";
$categories_result = $conn->query($sql_categories);
$categories = [];
$category_counts = [];
while ($row = $categories_result->fetch_assoc()) {
    $categories[] = $row;
    $category_counts[$row['name']] = $row['book_count'];
}
// Calculate total store value
$sql_total_value = "SELECT SUM(price) as total_value FROM books";
$total_value_result = $conn->query($sql_total_value);
$total_value = $total_value_result->fetch_assoc()['total_value'];
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bookstore Admin Dashboard</title>
    <link rel="icon" href="assets/icons/favicon.ico">

    <!-- Include Chart.js from CDN -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
    <style>
        /* Basic styling */
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f5f5f5;
        }
        
        .dashboard-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            background-color: #fff;
            padding: 15px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .header h1 {
            margin: 0;
            color: #333;
        }
        
        .user-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .action-btn-top, .logout-btn {
            padding: 8px 15px;
            border-radius: 5px;
            text-decoration: none;
            font-weight: bold;
        }
        
        .action-btn-top {
            background-color: #4CAF50;
            color: white;
        }
        
        .logout-btn {
            background-color: #f44336;
            color: white;
        }
        
        .stats-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .stat-value {
            font-size: 28px;
            font-weight: bold;
            color: #333;
            margin-bottom: 5px;
        }
        
        .stat-label {
            color: #666;
            font-size: 14px;
        }
        
        .categories-container {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .categories-title {
            margin-top: 0;
            color: #333;
        }
        
        .category-item {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
        }
        
        .category-name {
            width: 150px;
            font-weight: bold;
        }
        
        .category-bar-container {
            flex-grow: 1;
            height: 20px;
            background-color: #e0e0e0;
            border-radius: 10px;
            overflow: hidden;
            margin: 0 15px;
        }
        
        .category-bar {
            height: 100%;
            background-color: #2196F3;
            border-radius: 10px;
        }
        
        .category-count {
            width: 50px;
            text-align: right;
            font-weight: bold;
        }
        
        /* Management Panels */
        .management-panels {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .panel {
            background-color: #fff;
            padding: 25px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            text-align: center;
        }
        
        .panel h2 {
            margin-top: 0;
            color: #333;
            margin-bottom: 20px;
        }
        
        .panel-icon {
            font-size: 48px;
            margin-bottom: 15px;
            color: #2196F3;
        }
        
        .panel-actions {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin-top: 20px;
        }
        
        .panel-btn {
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            background-color: #2196F3;
            color: white;
            cursor: pointer;
            text-decoration: none;
            font-weight: bold;
            transition: background-color 0.3s;
        }
        
        .panel-btn:hover {
            background-color: #0b7dda;
        }

        /* New styles for graphs */
        .graphs-container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .graph-panel {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .graph-title {
            margin-top: 0;
            color: #333;
            text-align: center;
            margin-bottom: 20px;
        }
        
        .period-selector {
            display: flex;
            justify-content: center;
            margin-bottom: 15px;
        }
        
        .period-btn {
            padding: 8px 15px;
            margin: 0 5px;
            background-color: #e0e0e0;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        
        .period-btn.active {
            background-color: #2196F3;
            color: white;
        }
        
        .chart-container {
            height: 300px;
            width: 100%;
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <header class="header">
            <h1>Bookstore Admin Dashboard</h1>
            <div class="user-info">
                <span>Admin User</span>
                <a href="../forms/add_author.php" class="action-btn-top add-author-btn">Add Author</a>
                <a href="../forms/add_book.php" class="action-btn-top add-book-btn">Add Book</a>
                <a href="logout.php" class="logout-btn">Logout</a>
            </div>
        </header>
       
        <div class="stats-container">
            <div class="stat-card">
                <div class="stat-value"><?php echo $total_books; ?></div>
                <div class="stat-label">Total Books</div>
            </div>
           
            <div class="stat-card">
                <div class="stat-value"><?php echo $total_authors; ?></div>
                <div class="stat-label">Total Authors</div>
            </div>
           
            <div class="stat-card">
                <div class="stat-value">$<?php echo number_format($total_value, 2); ?></div>
                <div class="stat-label">Inventory Value</div>
            </div>
           
            <div class="stat-card">
                <div class="stat-value"><?php echo count($categories); ?></div>
                <div class="stat-label">Categories</div>
            </div>
        </div>

        <!-- New Graph Panels -->
        <div class="graphs-container">
            <!-- Sales Graph Panel -->
            <div class="graph-panel">
                <h2 class="graph-title">Book Sales</h2>
                <div class="period-selector" id="sales-period-selector">
                    <button class="period-btn active" data-period="weekly">Weekly</button>
                    <button class="period-btn" data-period="monthly">Monthly</button>
                    <button class="period-btn" data-period="yearly">Yearly</button>
                </div>
                <div class="chart-container">
                    <canvas id="salesChart"></canvas>
                </div>
            </div>
            
            <!-- Income Graph Panel -->
            <div class="graph-panel">
                <h2 class="graph-title">Total Income</h2>
                <div class="period-selector" id="income-period-selector">
                    <button class="period-btn active" data-period="weekly">Weekly</button>
                    <button class="period-btn" data-period="monthly">Monthly</button>
                    <button class="period-btn" data-period="yearly">Yearly</button>
                </div>
                <div class="chart-container">
                    <canvas id="incomeChart"></canvas>
                </div>
            </div>
        </div>
        
        <!-- Management Panels -->
        <div class="management-panels">
            <!-- Book Management Panel -->
            <div class="panel">
                <div class="panel-icon">📚</div>
                <h2>Book Management</h2>
                <p>Manage your bookstore inventory with full CRUD operations</p>
                <div class="panel-actions">
                    <a href="manage_books.php" class="panel-btn">Manage Books</a>
                </div>
            </div>
            
            <!-- Author Management Panel -->
            <div class="panel">
                <div class="panel-icon">✍️</div>
                <h2>Author Management</h2>
                <p>Add, edit, view, and delete authors in your database</p>
                <div class="panel-actions">
                    <a href="manage_authors.php" class="panel-btn">Manage Authors</a>
                </div>
            </div>
        </div>
       
        <div class="categories-container">
            <h2 class="categories-title">Books by Category</h2>
            <div class="category-bars">
                <?php foreach ($categories as $category):
                    $percentage = ($total_books > 0) ? ($category['book_count'] / $total_books) * 100 : 0;
                    $category_class = strtolower(str_replace(' ', '-', $category['name']));
                ?>
                <div class="category-item">
                    <div class="category-name"><?php echo htmlspecialchars($category['name']); ?></div>
                    <div class="category-bar-container">
                        <div class="category-bar" style="width: <?php echo $percentage; ?>%"></div>
                    </div>
                    <div class="category-count"><?php echo $category['book_count']; ?></div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <script>
        // JavaScript for dynamic dashboard features and charts
        document.addEventListener('DOMContentLoaded', function() {
            // Confirmation for logout
            document.querySelector('.logout-btn').addEventListener('click', function(e) {
                if (!confirm('Are you sure you want to logout?')) {
                    e.preventDefault();
                }
            });

            // Get the chart data from PHP
            const salesData = <?php echo $sales_json; ?>;
            const incomeData = <?php echo $income_json; ?>;
            
            // Initialize charts
            let salesChart = null;
            let incomeChart = null;
            
            // Initialize charts with weekly data by default
            initializeCharts('weekly');
            
            // Add event listeners for period selectors
            document.querySelectorAll('#sales-period-selector .period-btn').forEach(button => {
                button.addEventListener('click', function() {
                    const period = this.getAttribute('data-period');
                    updateActiveButton('#sales-period-selector', this);
                    updateChart(salesChart, salesData[period].labels, salesData[period].data, 'Books Sold');
                });
            });
            
            document.querySelectorAll('#income-period-selector .period-btn').forEach(button => {
                button.addEventListener('click', function() {
                    const period = this.getAttribute('data-period');
                    updateActiveButton('#income-period-selector', this);
                    updateChart(incomeChart, incomeData[period].labels, incomeData[period].data, 'Income ($)', true);
                });
            });
            
            // Function to initialize both charts
            function initializeCharts(period) {
                // Create Sales Chart
                const salesCtx = document.getElementById('salesChart').getContext('2d');
                salesChart = new Chart(salesCtx, {
                    type: 'bar',
                    data: {
                        labels: salesData[period].labels,
                        datasets: [{
                            label: 'Books Sold',
                            data: salesData[period].data,
                            backgroundColor: 'rgba(54, 162, 235, 0.6)',
                            borderColor: 'rgba(54, 162, 235, 1)',
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    precision: 0 // No decimal places for book count
                                }
                            }
                        }
                    }
                });
                
                // Create Income Chart
                const incomeCtx = document.getElementById('incomeChart').getContext('2d');
                incomeChart = new Chart(incomeCtx, {
                    type: 'line',
                    data: {
                        labels: incomeData[period].labels,
                        datasets: [{
                            label: 'Income ($)',
                            data: incomeData[period].data,
                            backgroundColor: 'rgba(75, 192, 192, 0.2)',
                            borderColor: 'rgba(75, 192, 192, 1)',
                            borderWidth: 2,
                            tension: 0.2,
                            fill: true
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    callback: function(value) {
                                        return '$' + value;
                                    }
                                }
                            }
                        }
                    }
                });
            }
            
            // Function to update active button
            function updateActiveButton(selector, activeButton) {
                document.querySelectorAll(`${selector} .period-btn`).forEach(btn => {
                    btn.classList.remove('active');
                });
                activeButton.classList.add('active');
            }
            
            // Function to update chart data
            function updateChart(chart, labels, data, label, isCurrency = false) {
                chart.data.labels = labels;
                chart.data.datasets[0].data = data;
                
                if (isCurrency) {
                    chart.options.scales.y.ticks.callback = function(value) {
                        return '$' + value;
                    };
                } else {
                    chart.options.scales.y.ticks.callback = undefined;
                }
                
                chart.update();
            }
        });
    </script>
</body>
</html>