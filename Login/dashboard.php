<?php
session_start();

// Prevent caching
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Add this at the beginning of the file after session_start()
require_once 'config.php';

// Function to get records from database
function getRecords($user_id) {
    global $conn;
    $sql = "SELECT * FROM search_records WHERE user_id = ? ORDER BY timestamp DESC LIMIT 5";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    return $stmt->get_result();
}

// Function to delete record
if (isset($_POST['delete_record']) && isset($_POST['record_id'])) {
    $record_id = (int)$_POST['record_id'];
    $user_id = $_SESSION['user_id'];
    
    // First verify the record belongs to the user
    $check_sql = "SELECT id FROM search_records WHERE id = ? AND user_id = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("ii", $record_id, $user_id);
    $check_stmt->execute();
    $result = $check_stmt->get_result();
    
    if ($result->num_rows > 0) {
        // Record exists and belongs to user, proceed with deletion
        $sql = "DELETE FROM search_records WHERE id = ? AND user_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $record_id, $user_id);
        
        if ($stmt->execute()) {
            $_SESSION['success_message'] = "Record deleted successfully";
        } else {
            $_SESSION['error_message'] = "Error deleting record: " . $conn->error;
        }
    } else {
        $_SESSION['error_message'] = "Record not found or unauthorized";
    }
    
    // Redirect to prevent form resubmission
    header('Location: dashboard.php');
    exit;
}

// Display success/error messages if they exist
if (isset($_SESSION['success_message'])) {
    echo '<div class="alert alert-success alert-dismissible fade show" role="alert">';
    echo $_SESSION['success_message'];
    echo '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>';
    echo '</div>';
    unset($_SESSION['success_message']);
}

if (isset($_SESSION['error_message'])) {
    echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">';
    echo $_SESSION['error_message'];
    echo '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>';
    echo '</div>';
    unset($_SESSION['error_message']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <title>Food Safety Checker Dashboard</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #6C63FF;
            --secondary-color: #4CAF50;
            --accent-color: #FF6B6B;
            --background-color: #F8F9FF;
            --card-bg: #FFFFFF;
            --text-primary: #2D3436;
            --text-secondary: #636E72;
        }

        body {
            background: var(--background-color);
            font-family: 'Poppins', sans-serif;
            color: var(--text-primary);
        }

        .navbar {
            background: linear-gradient(135deg, var(--primary-color), #8A84FF) !important;
            box-shadow: 0 4px 20px rgba(108, 99, 255, 0.2);
            padding: 1rem 0;
        }

        .navbar-brand {
            font-size: 2rem;
            font-weight: 700;
            color: white !important;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.1);
        }

        .card {
            border: none;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
            background: var(--card-bg);
            overflow: hidden;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.1);
        }

        .card-header {
            background: linear-gradient(135deg, #ffffff, #f8f9fa);
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
            border-radius: 20px 20px 0 0 !important;
            padding: 1.5rem;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary-color), #8A84FF);
            border: none;
            border-radius: 12px;
            padding: 12px 30px;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(108, 99, 255, 0.3);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(108, 99, 255, 0.4);
        }

        .form-control {
            border-radius: 12px;
            padding: 15px 20px;
            border: 2px solid #E9ECEF;
            transition: all 0.3s ease;
            font-size: 1rem;
        }

        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(108, 99, 255, 0.25);
        }

        .badge {
            padding: 8px 15px;
            border-radius: 10px;
            font-weight: 600;
            font-size: 0.9rem;
        }

        .record-item {
            background: #F8F9FF;
            border-radius: 15px;
            padding: 1rem;
            margin-bottom: 0.5rem;
            transition: all 0.3s ease;
            border: none;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
            cursor: pointer;
            border-left: 4px solid #ccc;
        }

        .record-item:hover {
            transform: translateX(5px);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.1);
            background: #FFFFFF;
        }

        .record-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .record-ingredients {
            font-weight: 500;
            color: #2D3436;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 70%;
        }

        .record-timestamp {
            font-size: 0.8rem;
            color: #636E72;
        }

        .record-badge {
            margin-left: 10px;
            font-size: 0.8rem;
            padding: 5px 10px;
        }

        .info-section {
            background: rgba(255, 255, 255, 0.8);
            border-radius: 12px;
            padding: 1.25rem;
            margin-bottom: 1rem;
        }

        .info-section-title {
            font-weight: 600;
            color: #2D3436;
            margin-bottom: 0.75rem;
        }

        .records-container {
            max-height: 100px;
            overflow-y: auto;
            padding-right: 10px;
        }

        .records-container::-webkit-scrollbar {
            width: 6px;
        }

        .records-container::-webkit-scrollbar-track {
            background: #F1F1F1;
            border-radius: 3px;
        }

        .records-container::-webkit-scrollbar-thumb {
            background: var(--primary-color);
            border-radius: 3px;
        }

        .records-container::-webkit-scrollbar-thumb:hover {
            background: #8A84FF;
        }

        .title-font {
            font-family: 'Poppins', sans-serif;
            font-weight: 700;
            color: var(--text-primary);
            font-size: 1.5rem;
        }

        .btn-outline-danger {
            border-radius: 12px;
            padding: 10px 25px;
            font-weight: 600;
            transition: all 0.3s ease;
            border: 2px solid var(--accent-color);
            color: var(--accent-color);
        }

        .btn-outline-danger:hover {
            background: var(--accent-color);
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(255, 107, 107, 0.3);
        }

        .card-text {
            color: #2D3436;
        }

        /* Update result card styles */
        .result-card {
            background: #FFFFFF;
            border-radius: 15px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
            border-left: 4px solid #ccc;
        }

        .result-card.high-risk {
            border-left-color: #dc3545;
            background: #fff5f5;
        }

        .result-card.medium-risk {
            border-left-color: #ffc107;
            background: #fff9e6;
        }

        .result-card.low-risk {
            border-left-color: #28a745;
            background: #f0fff4;
        }

        /* Update record item styles */
        .record-item.high-risk {
            border-left-color: #dc3545;
            background: #fff5f5;
        }

        .record-item.medium-risk {
            border-left-color: #ffc107;
            background: #fff9e6;
        }

        .record-item.low-risk {
            border-left-color: #28a745;
            background: #f0fff4;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="#">🛡️</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="dashboard.php">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="logout.php">Logout</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="row">
            <div class="col-md-8">
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <h4 class="card-title mb-0 title-font">Ingredient Checker</h4>
                        <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#harmfulIngredientsModal">
                            <i class="fas fa-exclamation-triangle me-2"></i>View Harmful Ingredients
                        </button>
                    </div>
                    <div class="card-body">
                        <form id="ingredientForm">
                            <div class="mb-3">
                                <label for="ingredientList" class="form-label">Enter ingredients or categories (one per line)</label>
                                <textarea class="form-control" id="ingredientList" rows="6" placeholder="Example:
ARTIFICIAL FLAVORS
ASPARTAME
RED 40"></textarea>
                                <div class="form-text">You can search for specific ingredients or categories like: ARTIFICIAL FLAVORS, ARTIFICIAL SWEETENERS, HEAVY METALS, etc.</div>
                            </div>
                            <button type="submit" class="btn btn-primary">Check Ingredients</button>
                        </form>
                    </div>
                </div>

                <div class="card shadow-sm mb-4" id="resultsCard" style="display: none;">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <h4 class="card-title mb-0">Results</h4>
                        <span class="badge bg-danger" id="highRiskCount">0 High Risk</span>
                    </div>
                    <div class="card-body">
                        <div id="resultsContainer"></div>
                    </div>
                </div>

                <!-- Results Modal -->
                <div class="modal fade" id="resultsModal" tabindex="-1">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Search Results</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <div id="modalResultsContainer"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Harmful Ingredients Modal -->
                <div class="modal fade" id="harmfulIngredientsModal" tabindex="-1">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Harmful Ingredients List</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Ingredient</th>
                                                <th>Category</th>
                                                <th>Risk Level</th>
                                                <th>Health Impact</th>
                                                <th>Safer Alternatives</th>
                                            </tr>
                                        </thead>
                                        <tbody id="harmfulIngredientsList">
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <!-- Risk Level Guide -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-white">
                        <h5 class="card-title mb-0 text-center">Risk Level Guide</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                            <div class="badge bg-danger me-2">High</div>
                            <div>May cause serious health issues</div>
                        </div>
                        <div class="d-flex align-items-center mb-3">
                            <div class="badge bg-warning text-dark me-2">Medium</div>
                            <div>May cause moderate health issues</div>
                        </div>
                        <div class="d-flex align-items-center">
                            <div class="badge bg-success me-2">Low</div>
                            <div>Generally recognized as safe</div>
                        </div>
                    </div>
                </div>

                <!-- Records Section -->
                <div class="card shadow-sm">
                    <div class="card-header bg-white">
                        <h5 class="card-title mb-0 text-center">Recent Records</h5>
                    </div>
                    <div class="card-body">
                        <div class="records-container">
                            <div id="recentScans" class="list-group list-group-flush">
                                <?php
                                $records = getRecords($_SESSION['user_id']);
                                if ($records->num_rows === 0) {
                                    echo '<div class="text-center text-muted"><p>No records yet</p></div>';
                                } else {
                                    while ($record = $records->fetch_assoc()) {
                                        echo '<div class="list-group-item record-item" data-id="' . $record['id'] . '">';
                                        echo '<div class="record-content">';
                                        echo '<div class="ingredients">' . htmlspecialchars($record['ingredients']) . '</div>';
                                        echo '<div class="timestamp">' . date('M d, Y H:i', strtotime($record['timestamp'])) . '</div>';
                                        echo '</div>';
                                        echo '<span class="badge ' . ($record['high_risk_count'] > 0 ? 'bg-danger' : 'bg-success') . ' record-badge">';
                                        echo $record['high_risk_count'] . ' High Risk';
                                        echo '</span>';
                                        echo '</div>';
                                    }
                                }
                                ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Risk Level Modal -->
    <div class="modal fade" id="riskLevelModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Risk Level Information</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="riskLevelContent"></div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Initialize Bootstrap modal
        document.addEventListener('DOMContentLoaded', function() {
            const modal = new bootstrap.Modal(document.getElementById('harmfulIngredientsModal'));
            
            // Make modal available globally
            window.harmfulIngredientsModal = modal;
        });

        document.addEventListener('DOMContentLoaded', function() {
            const ingredientForm = document.getElementById('ingredientForm');
            const resultsCard = document.getElementById('resultsCard');
            const resultsContainer = document.getElementById('resultsContainer');
            const highRiskCount = document.getElementById('highRiskCount');
            
            // Ingredient database (simplified version)
            const ingredientDatabase = {
                // Artificial Sweeteners
                "ASPARTAME": { category: "Artificial Sweetener", risk: "High", impact: "May cause neurological issues, headaches, and cancer", alternatives: "Stevia, Monk Fruit, Erythritol" },
                "SACCHARIN": { category: "Artificial Sweetener", risk: "High", impact: "May cause cancer and bladder issues", alternatives: "Stevia, Monk Fruit, Erythritol" },
                "SUCRALOSE": { category: "Artificial Sweetener", risk: "Medium", impact: "May affect gut health and insulin response", alternatives: "Stevia, Monk Fruit, Erythritol" },
                "ACESULFAME K": { category: "Artificial Sweetener", risk: "High", impact: "May cause cancer and affect thyroid function", alternatives: "Stevia, Monk Fruit, Erythritol" },
                "NEOTAME": { category: "Artificial Sweetener", risk: "High", impact: "May cause neurological issues and metabolic problems", alternatives: "Stevia, Monk Fruit, Erythritol" },

                // Artificial Colors
                "RED 40": { category: "Artificial Color", risk: "High", impact: "May cause hyperactivity, cancer, and allergic reactions", alternatives: "Beet juice, Paprika extract" },
                "RED 3": { category: "Artificial Color", risk: "High", impact: "May cause cancer and thyroid issues", alternatives: "Beet juice, Paprika extract" },
                "YELLOW 5": { category: "Artificial Color", risk: "High", impact: "May cause allergic reactions and hyperactivity", alternatives: "Turmeric, Annatto" },
                "YELLOW 6": { category: "Artificial Color", risk: "High", impact: "May cause cancer and allergic reactions", alternatives: "Turmeric, Annatto" },
                "BLUE 1": { category: "Artificial Color", risk: "Medium", impact: "May cause allergic reactions and behavioral issues", alternatives: "Spirulina, Butterfly pea flower" },
                "BLUE 2": { category: "Artificial Color", risk: "Medium", impact: "May cause brain tumors and hyperactivity", alternatives: "Spirulina, Butterfly pea flower" },
                "GREEN 3": { category: "Artificial Color", risk: "High", impact: "May cause cancer and bladder tumors", alternatives: "Chlorophyll, Matcha" },

                // Artificial Flavors
                "ARTIFICIAL VANILLA": { category: "Artificial Flavor", risk: "Medium", impact: "May contain harmful chemicals and cause allergic reactions", alternatives: "Pure vanilla extract" },
                "ARTIFICIAL BUTTER FLAVOR": { category: "Artificial Flavor", risk: "High", impact: "Contains diacetyl which may cause lung damage", alternatives: "Real butter, Ghee" },
                "ARTIFICIAL STRAWBERRY": { category: "Artificial Flavor", risk: "Medium", impact: "May contain harmful chemicals and cause allergic reactions", alternatives: "Real strawberry extract" },

                // Natural Flavors
                "NATURAL FLAVORS": { category: "Natural Flavor", risk: "Medium", impact: "May contain hidden MSG and other additives", alternatives: "Whole food ingredients" },
                "NATURAL VANILLA FLAVOR": { category: "Natural Flavor", risk: "Low", impact: "Generally safe but may contain additives", alternatives: "Pure vanilla extract" },

                // Heavy Metals
                "LEAD": { category: "Heavy Metal", risk: "High", impact: "Causes neurological damage, especially in children", alternatives: "Lead-free products" },
                "MERCURY": { category: "Heavy Metal", risk: "High", impact: "Causes neurological damage and developmental issues", alternatives: "Mercury-free products" },
                "CADMIUM": { category: "Heavy Metal", risk: "High", impact: "May cause kidney damage and cancer", alternatives: "Cadmium-free products" },
                "ARSENIC": { category: "Heavy Metal", risk: "High", impact: "May cause cancer and skin damage", alternatives: "Arsenic-free products" },

                // Preservatives
                "BHA": { category: "Preservative", risk: "High", impact: "May cause cancer and behavioral issues", alternatives: "Vitamin E, Rosemary extract" },
                "BHT": { category: "Preservative", risk: "High", impact: "May cause cancer and liver damage", alternatives: "Vitamin E, Rosemary extract" },
                "SODIUM NITRATE": { category: "Preservative", risk: "High", impact: "May cause cancer and heart disease", alternatives: "Celery powder, Sea salt" },
                "SODIUM NITRITE": { category: "Preservative", risk: "High", impact: "May cause cancer and heart disease", alternatives: "Celery powder, Sea salt" },
                "SODIUM BENZOATE": { category: "Preservative", risk: "Medium", impact: "May cause hyperactivity and allergic reactions", alternatives: "Vitamin C, Citric acid" },

                // Other Additives
                "MSG": { category: "Additive", risk: "Medium", impact: "May cause headaches and allergic reactions", alternatives: "Sea salt, Herbs and spices" },
                "HIGH FRUCTOSE CORN SYRUP": { category: "Additive", risk: "High", impact: "May cause obesity, diabetes, and liver damage", alternatives: "Honey, Maple syrup" },
                "CARRAGEENAN": { category: "Additive", risk: "Medium", impact: "May cause inflammation and digestive issues", alternatives: "Agar agar, Pectin" },
                "MALTODEXTRIN": { category: "Additive", risk: "Medium", impact: "May affect blood sugar and gut health", alternatives: "Whole grains, Natural sweeteners" }
            };

            // Category database with general information
            const categoryDatabase = {
                "ARTIFICIAL SWEETENERS": {
                    description: "Synthetic sugar substitutes that provide sweetness without calories",
                    generalRisks: "May cause neurological issues, metabolic problems, and potential cancer risks",
                    commonUses: "Diet sodas, sugar-free products, low-calorie foods",
                    saferAlternatives: "Natural sweeteners like Stevia, Monk Fruit, or Erythritol",
                    relatedCategories: ["Natural Sweeteners", "Sugar Substitutes"],
                    risk: "High"
                },
                "ARTIFICIAL COLORS": {
                    description: "Synthetic dyes used to enhance or change food appearance",
                    generalRisks: "May cause hyperactivity, allergic reactions, and potential cancer risks",
                    commonUses: "Candies, processed foods, beverages",
                    saferAlternatives: "Natural colorants like beet juice, turmeric, or spirulina",
                    relatedCategories: ["Natural Colors", "Food Dyes"],
                    risk: "High"
                },
                "ARTIFICIAL FLAVORS": {
                    description: "Synthetic chemicals designed to mimic natural tastes",
                    generalRisks: "May cause allergic reactions, hyperactivity, and potential long-term health effects",
                    commonUses: "Processed foods, candies, soft drinks",
                    saferAlternatives: "Natural extracts (vanilla bean, citrus oils) or whole ingredients",
                    relatedCategories: ["Natural Flavors", "Food Flavors"],
                    risk: "High"
                },
                "NATURAL FLAVORS": {
                    description: "Flavorings derived from natural sources but may still contain additives",
                    generalRisks: "May contain hidden MSG and other additives",
                    commonUses: "Various processed foods and beverages",
                    saferAlternatives: "Whole food ingredients and pure extracts",
                    relatedCategories: ["Artificial Flavors", "Food Flavors"],
                    risk: "Medium"
                },
                "HEAVY METALS": {
                    description: "Toxic metallic elements that can accumulate in the body",
                    generalRisks: "May cause neurological damage, developmental issues, and cancer",
                    commonUses: "Contaminants in food and water",
                    saferAlternatives: "Choose products tested for heavy metals",
                    relatedCategories: ["Toxins", "Environmental Contaminants"],
                    risk: "High"
                },
                "PRESERVATIVES": {
                    description: "Substances added to prevent food spoilage and extend shelf life",
                    generalRisks: "May cause allergic reactions, cancer, and other health issues",
                    commonUses: "Processed foods, meats, beverages",
                    saferAlternatives: "Natural preservatives like vitamin E, rosemary extract",
                    relatedCategories: ["Food Additives", "Natural Preservatives"],
                    risk: "High"
                }
            };

            let searchHistory = [];

            // Update the ingredient form submission handler
            ingredientForm.addEventListener('submit', async function(e) {
                e.preventDefault();
                
                const ingredientText = document.getElementById('ingredientList').value;
                const ingredients = ingredientText.split('\n')
                    .map(item => item.trim().toUpperCase())
                    .filter(item => item.length > 0);
                
                if (ingredients.length === 0) {
                    alert('Please enter at least one ingredient or category');
                    return;
                }
                
                // Process ingredients immediately
                const results = [];
                let highRiskItems = 0;
                
                ingredients.forEach(ingredient => {
                    if (categoryDatabase[ingredient]) {
                        results.push({
                            name: ingredient,
                            found: true,
                            isCategory: true,
                            data: categoryDatabase[ingredient]
                        });
                        if (categoryDatabase[ingredient].risk === "High") {
                            highRiskItems++;
                        }
                    } else if (ingredientDatabase[ingredient]) {
                        results.push({
                            name: ingredient,
                            found: true,
                            isCategory: false,
                            data: ingredientDatabase[ingredient]
                        });
                        
                        if (ingredientDatabase[ingredient].risk === "High") {
                            highRiskItems++;
                        }
                    } else {
                        results.push({
                            name: ingredient,
                            found: false
                        });
                    }
                });
                
                // Display results immediately
                displayResults(results, highRiskItems);
                
                // Save record in the background
                const formData = new FormData();
                formData.append('ingredients', ingredients.join('\n'));
                formData.append('high_risk_count', highRiskItems);
                
                try {
                    const response = await fetch('save_record.php', {
                        method: 'POST',
                        body: formData
                    });
                    
                    if (!response.ok) {
                        throw new Error('Failed to save record');
                    }
                    
                    const data = await response.json();
                    if (data.success) {
                        // Update records display
                        updateRecordsDisplay();
                    } else {
                        console.error('Error saving record:', data.message);
                    }
                } catch (error) {
                    console.error('Error:', error);
                }
            });
            
            // Function to update records display
            async function updateRecordsDisplay() {
                try {
                    const response = await fetch('get_records.php');
                    if (!response.ok) {
                        throw new Error('Failed to fetch records');
                    }
                    const data = await response.json();
                    
                    if (data.success) {
                        const recordsContainer = document.querySelector('.records-container');
                        if (recordsContainer) {
                            // Show all records
                            recordsContainer.innerHTML = data.records.map(record => {
                                const riskClass = record.high_risk_count > 0 ? 'high-risk' : 'low-risk';
                                return `
                                    <div class="record-item ${riskClass}" data-record-id="${record.id}">
                                        <div class="record-content">
                                            <div class="record-ingredients">${record.ingredients}</div>
                                            <div class="record-timestamp">${record.timestamp}</div>
                                        </div>
                                        <span class="badge ${record.high_risk_count > 0 ? 'bg-danger' : 'bg-success'} record-badge">
                                            ${record.high_risk_count} High Risk
                                        </span>
                                    </div>
                                `;
                            }).join('');

                            // Add click handlers to all records
                            document.querySelectorAll('.record-item').forEach(item => {
                                item.addEventListener('click', function() {
                                    const ingredients = this.querySelector('.record-ingredients').textContent;
                                    document.getElementById('ingredientList').value = ingredients;
                                    document.getElementById('ingredientForm').dispatchEvent(new Event('submit'));
                                });
                            });
                        }
                    }
                } catch (error) {
                    console.error('Error updating records:', error);
                }
            }
            
            function displayResults(results, highRiskCount) {
                const modalResultsContainer = document.getElementById('modalResultsContainer');
                modalResultsContainer.innerHTML = '';
                
                // Add results summary
                const summaryHtml = `
                    <div class="results-summary">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">Search Results Summary</h5>
                            <span class="badge ${highRiskCount > 0 ? 'bg-danger' : 'bg-success'}">
                                ${highRiskCount} High Risk Items Found
                            </span>
                        </div>
                    </div>
                `;
                modalResultsContainer.innerHTML = summaryHtml;
                
                results.forEach(result => {
                    let cardClass = 'result-card';
                    let badgeClass = 'bg-secondary';
                    let badgeText = 'Not Found';
                    let detailsHtml = '<p class="card-text">This ingredient or category was not found in our database.</p>';
                    
                    if (result.found) {
                        if (result.isCategory) {
                            if (result.data.risk === "High") {
                                cardClass += ' high-risk';
                                badgeClass = 'bg-danger';
                            } else if (result.data.risk === "Medium") {
                                cardClass += ' medium-risk';
                                badgeClass = 'bg-warning text-dark';
                            } else {
                                cardClass += ' low-risk';
                                badgeClass = 'bg-success';
                            }
                            badgeText = result.data.risk + ' Risk Category';
                            
                            detailsHtml = `
                                <div class="info-section">
                                    <div class="info-section-title">Description</div>
                                    <p class="card-text">${result.data.description}</p>
                                </div>
                                <div class="info-section">
                                    <div class="info-section-title">Risk Level</div>
                                    <p class="card-text"><span class="badge ${badgeClass}">${result.data.risk}</span></p>
                                </div>
                                <div class="info-section">
                                    <div class="info-section-title">General Risks</div>
                                    <p class="card-text">${result.data.generalRisks}</p>
                                </div>
                                <div class="info-section">
                                    <div class="info-section-title">Common Uses</div>
                                    <p class="card-text">${result.data.commonUses}</p>
                                </div>
                                <div class="info-section">
                                    <div class="info-section-title">Safer Alternatives</div>
                                    <p class="card-text">${result.data.saferAlternatives}</p>
                                </div>
                                <div class="info-section">
                                    <div class="info-section-title">Related Categories</div>
                                    <p class="card-text">${result.data.relatedCategories.join(', ')}</p>
                                </div>
                            `;
                        } else {
                            if (result.data.risk === "High") {
                                cardClass += ' high-risk';
                                badgeClass = 'bg-danger';
                            } else if (result.data.risk === "Medium") {
                                cardClass += ' medium-risk';
                                badgeClass = 'bg-warning text-dark';
                            } else {
                                cardClass += ' low-risk';
                                badgeClass = 'bg-success';
                            }
                            
                            badgeText = result.data.risk + ' Risk';
                            
                            detailsHtml = `
                                <div class="info-section">
                                    <div class="info-section-title">Category</div>
                                    <p class="card-text">${result.data.category}</p>
                                </div>
                                <div class="info-section">
                                    <div class="info-section-title">Health Impact</div>
                                    <p class="card-text">${result.data.impact}</p>
                                </div>
                                <div class="info-section">
                                    <div class="info-section-title">Safer Alternatives</div>
                                    <p class="card-text">${result.data.alternatives}</p>
                                </div>
                            `;
                        }
                    }
                    
                    const resultHtml = `
                        <div class="${cardClass}">
                            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                                <h5 class="card-title mb-0">${result.name}</h5>
                                <span class="badge ${badgeClass}">${badgeText}</span>
                            </div>
                            <div class="card-body">
                                ${detailsHtml}
                            </div>
                        </div>
                    `;
                    modalResultsContainer.innerHTML += resultHtml;
                });
                
                // Show the results modal
                const resultsModal = new bootstrap.Modal(document.getElementById('resultsModal'));
                resultsModal.show();
            }

            // Function to show harmful ingredients
            function showHarmfulIngredients() {
                const harmfulIngredientsList = document.getElementById('harmfulIngredientsList');
                harmfulIngredientsList.innerHTML = '';
                
                // Filter high and medium risk ingredients
                const harmfulIngredients = Object.entries(ingredientDatabase)
                    .filter(([_, data]) => data.risk === "High" || data.risk === "Medium")
                    .sort((a, b) => {
                        // Sort by risk level (High first, then Medium)
                        if (a[1].risk === "High" && b[1].risk === "Medium") return -1;
                        if (a[1].risk === "Medium" && b[1].risk === "High") return 1;
                        return 0;
                    });
                
                harmfulIngredients.forEach(([name, data]) => {
                    const row = document.createElement('tr');
                    row.innerHTML = `
                        <td><strong>${name}</strong></td>
                        <td>${data.category}</td>
                        <td><span class="badge ${data.risk === 'High' ? 'bg-danger' : 'bg-warning text-dark'}">${data.risk}</span></td>
                        <td>${data.impact}</td>
                        <td>${data.alternatives}</td>
                    `;
                    harmfulIngredientsList.appendChild(row);
                });
            }

            // Add event listener for modal show
            const harmfulIngredientsModal = document.getElementById('harmfulIngredientsModal');
            harmfulIngredientsModal.addEventListener('show.bs.modal', showHarmfulIngredients);

            // Add this at the beginning of your script section
            document.addEventListener('DOMContentLoaded', function() {
                // Risk Level Guide Modal Handler
                const riskLevelModal = document.getElementById('riskLevelModal');
                const riskLevelContent = document.getElementById('riskLevelContent');
                
                riskLevelModal.addEventListener('show.bs.modal', function(event) {
                    const button = event.relatedTarget;
                    const riskLevel = button.getAttribute('data-risk');
                    
                    let content = '';
                    switch(riskLevel) {
                        case 'High':
                            content = `
                                <div class="alert alert-danger">
                                    <h6 class="alert-heading">High Risk Level</h6>
                                    <p class="mb-0">These ingredients pose significant health risks and should be avoided. They may cause serious health problems, allergic reactions, or long-term health issues.</p>
                                </div>
                                <div class="mt-3">
                                    <h6>Common Effects:</h6>
                                    <ul>
                                        <li>Severe allergic reactions</li>
                                        <li>Long-term health complications</li>
                                        <li>Immediate adverse effects</li>
                                        <li>Potential carcinogenic properties</li>
                                    </ul>
                                </div>
                            `;
                            break;
                        case 'Medium':
                            content = `
                                <div class="alert alert-warning">
                                    <h6 class="alert-heading">Medium Risk Level</h6>
                                    <p class="mb-0">These ingredients may cause moderate health concerns and should be consumed with caution. They might cause mild reactions or have limited research on long-term effects.</p>
                                </div>
                                <div class="mt-3">
                                    <h6>Common Effects:</h6>
                                    <ul>
                                        <li>Mild allergic reactions</li>
                                        <li>Digestive discomfort</li>
                                        <li>Limited research on long-term effects</li>
                                        <li>Potential sensitivity issues</li>
                                    </ul>
                                </div>
                            `;
                            break;
                        case 'Low':
                            content = `
                                <div class="alert alert-success">
                                    <h6 class="alert-heading">Low Risk Level</h6>
                                    <p class="mb-0">These ingredients are generally safe for consumption and have minimal health risks. They are commonly used in food products and have been well-researched.</p>
                                </div>
                                <div class="mt-3">
                                    <h6>Common Effects:</h6>
                                    <ul>
                                        <li>Generally safe for most people</li>
                                        <li>Well-researched safety profile</li>
                                        <li>Minimal health concerns</li>
                                        <li>Commonly used in food products</li>
                                    </ul>
                                </div>
                            `;
                            break;
                    }
                    riskLevelContent.innerHTML = content;
                });
            });

            // Update the delete record function
            function deleteRecord(recordId) {
                if (confirm('Are you sure you want to delete this record?')) {
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = 'dashboard.php';
                    
                    const deleteInput = document.createElement('input');
                    deleteInput.type = 'hidden';
                    deleteInput.name = 'delete_record';
                    deleteInput.value = '1';
                    
                    const recordInput = document.createElement('input');
                    recordInput.type = 'hidden';
                    deleteInput.name = 'record_id';
                    recordInput.value = recordId;
                    
                    form.appendChild(deleteInput);
                    form.appendChild(recordInput);
                    
                    document.body.appendChild(form);
                    form.submit();
                }
            }

            // Update the record click handler
            document.addEventListener('DOMContentLoaded', function() {
                const recordItems = document.querySelectorAll('.record-item');
                recordItems.forEach(item => {
                    // Add click handler for the record item
                    item.addEventListener('click', function(e) {
                        if (!e.target.closest('.delete-record')) {
                            const ingredients = this.querySelector('.ingredients').textContent;
                            document.getElementById('ingredientList').value = ingredients;
                            document.getElementById('ingredientForm').dispatchEvent(new Event('submit'));
                        }
                    });

                    // Add click handler for the delete button
                    const deleteBtn = item.querySelector('.delete-record');
                    if (deleteBtn) {
                        deleteBtn.addEventListener('click', function(e) {
                            e.preventDefault();
                            e.stopPropagation();
                            const recordId = this.getAttribute('data-record-id');
                            deleteRecord(recordId);
                        });
                    }
                });
            });
        });
    </script>
</body>
</html>