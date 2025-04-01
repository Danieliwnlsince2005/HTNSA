document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('checkerForm');
    const results = document.getElementById('results');
    const resultsContent = document.getElementById('resultsContent');
    const ingredientModal = new bootstrap.Modal(document.getElementById('ingredientModal'));
    const modalContent = document.getElementById('modalContent');

    // Form submission
    form.addEventListener('submit', async function(event) {
        event.preventDefault();
        
        if (!form.checkValidity()) {
            event.stopPropagation();
            form.classList.add('was-validated');
            return;
        }

        const ingredients = document.getElementById('ingredients').value.trim();
        if (!ingredients) {
            return;
        }

        try {
            const response = await fetch('check_ingredients.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ ingredients: ingredients.split(',').map(i => i.trim()) })
            });

            const data = await response.json();
            
            if (data.success) {
                displayResults(data.results);
                results.style.display = 'block';
            } else {
                showError(data.message || 'An error occurred while checking ingredients.');
            }
        } catch (error) {
            console.error('Error:', error);
            showError('An error occurred while checking ingredients.');
        }
    });

    // Display results
    function displayResults(results) {
        let html = '';
        
        // Group results by risk level
        const groupedResults = {
            high: [],
            medium: [],
            low: [],
            safe: []
        };

        results.forEach(result => {
            if (result.risk_level === 'High') {
                groupedResults.high.push(result);
            } else if (result.risk_level === 'Medium') {
                groupedResults.medium.push(result);
            } else if (result.risk_level === 'Low') {
                groupedResults.low.push(result);
            } else {
                groupedResults.safe.push(result);
            }
        });

        // High risk ingredients
        if (groupedResults.high.length > 0) {
            html += `
                <div class="alert alert-danger mb-4">
                    <h5 class="alert-heading">High Risk Ingredients</h5>
                    <ul class="mb-0">
                        ${groupedResults.high.map(ingredient => `
                            <li>
                                <a href="#" class="text-danger" onclick="showIngredientDetails('${ingredient.name}')">
                                    ${ingredient.name}
                                </a>
                            </li>
                        `).join('')}
                    </ul>
                </div>
            `;
        }

        // Medium risk ingredients
        if (groupedResults.medium.length > 0) {
            html += `
                <div class="alert alert-warning mb-4">
                    <h5 class="alert-heading">Medium Risk Ingredients</h5>
                    <ul class="mb-0">
                        ${groupedResults.medium.map(ingredient => `
                            <li>
                                <a href="#" class="text-warning" onclick="showIngredientDetails('${ingredient.name}')">
                                    ${ingredient.name}
                                </a>
                            </li>
                        `).join('')}
                    </ul>
                </div>
            `;
        }

        // Low risk ingredients
        if (groupedResults.low.length > 0) {
            html += `
                <div class="alert alert-info mb-4">
                    <h5 class="alert-heading">Low Risk Ingredients</h5>
                    <ul class="mb-0">
                        ${groupedResults.low.map(ingredient => `
                            <li>
                                <a href="#" class="text-info" onclick="showIngredientDetails('${ingredient.name}')">
                                    ${ingredient.name}
                                </a>
                            </li>
                        `).join('')}
                    </ul>
                </div>
            `;
        }

        // Safe ingredients
        if (groupedResults.safe.length > 0) {
            html += `
                <div class="alert alert-success mb-4">
                    <h5 class="alert-heading">Safe Ingredients</h5>
                    <ul class="mb-0">
                        ${groupedResults.safe.map(ingredient => `
                            <li>
                                <a href="#" class="text-success" onclick="showIngredientDetails('${ingredient.name}')">
                                    ${ingredient.name}
                                </a>
                            </li>
                        `).join('')}
                    </ul>
                </div>
            `;
        }

        // No results found
        if (Object.values(groupedResults).every(arr => arr.length === 0)) {
            html = '<div class="alert alert-info">No matching ingredients found in our database.</div>';
        }

        resultsContent.innerHTML = html;
    }

    // Show ingredient details
    window.showIngredientDetails = async function(ingredientName) {
        try {
            const response = await fetch(`get_ingredient_details.php?name=${encodeURIComponent(ingredientName)}`);
            const data = await response.json();
            
            if (data.success) {
                const ingredient = data.ingredient;
                modalContent.innerHTML = `
                    <div class="mb-3">
                        <h6>Category</h6>
                        <p>${ingredient.category}</p>
                    </div>
                    <div class="mb-3">
                        <h6>Risk Level</h6>
                        <p class="text-${getRiskLevelColor(ingredient.risk_level)}">${ingredient.risk_level}</p>
                    </div>
                    <div class="mb-3">
                        <h6>Health Impact</h6>
                        <p>${ingredient.health_impact}</p>
                    </div>
                    <div class="mb-3">
                        <h6>Common Products</h6>
                        <p>${ingredient.common_products}</p>
                    </div>
                    <button class="btn btn-primary" onclick="addToFavorites('${ingredient.name}')">
                        <i class="bi bi-star"></i> Add to Favorites
                    </button>
                `;
                ingredientModal.show();
            } else {
                showError(data.message || 'Error fetching ingredient details.');
            }
        } catch (error) {
            console.error('Error:', error);
            showError('Error fetching ingredient details.');
        }
    };

    // Add to favorites
    window.addToFavorites = async function(ingredientName) {
        try {
            const response = await fetch('add_favorite.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ ingredient: ingredientName })
            });

            const data = await response.json();
            
            if (data.success) {
                showSuccess('Ingredient added to favorites!');
                ingredientModal.hide();
                // Refresh the page to update favorites list
                location.reload();
            } else {
                showError(data.message || 'Error adding to favorites.');
            }
        } catch (error) {
            console.error('Error:', error);
            showError('Error adding to favorites.');
        }
    };

    // Helper functions
    function getRiskLevelColor(riskLevel) {
        switch (riskLevel.toLowerCase()) {
            case 'high':
                return 'danger';
            case 'medium':
                return 'warning';
            case 'low':
                return 'info';
            default:
                return 'success';
        }
    }

    function showError(message) {
        const alert = document.createElement('div');
        alert.className = 'alert alert-danger alert-dismissible fade show';
        alert.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        resultsContent.innerHTML = '';
        resultsContent.appendChild(alert);
        results.style.display = 'block';
    }

    function showSuccess(message) {
        const alert = document.createElement('div');
        alert.className = 'alert alert-success alert-dismissible fade show';
        alert.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        resultsContent.innerHTML = '';
        resultsContent.appendChild(alert);
        results.style.display = 'block';
    }
}); 