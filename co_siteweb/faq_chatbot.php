<?php
// FAQ Chatbot - Popular Categories Only (FIXED v2)
?>

<div id="faq-chatbot-container" style="position: fixed; bottom: 20px; right: 20px; z-index: 99999; font-family: 'Segoe UI', sans-serif;">
    
    <!-- FLOATING BUTTON -->
    <button id="faq-btn" style="
        width: 60px; height: 60px; border-radius: 50%; 
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white; border: none; cursor: pointer; font-size: 1.5rem;
        box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
        transition: all 0.3s ease;
        display: flex; align-items: center; justify-content: center;
    ">
        💬
    </button>

    <!-- CHATBOT WIDGET -->
    <div id="faq-widget" style="
        position: fixed; bottom: 90px; right: 20px;
        width: 400px; max-height: 600px;
        background: white; border-radius: 12px;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
        display: none; flex-direction: column;
        z-index: 99998;
    ">
        <!-- HEADER -->
        <div style="
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white; padding: 1rem;
            border-radius: 12px 12px 0 0;
            display: flex; justify-content: space-between; align-items: center;
        ">
            <h3 style="margin: 0; font-size: 1.1rem;">🤖 FAQ Assistant</h3>
            <button id="faq-close" style="
                background: rgba(255,255,255,0.2); color: white;
                border: none; font-size: 1.5rem; cursor: pointer;
                width: 30px; height: 30px; border-radius: 50%;
            ">&times;</button>
        </div>

        <!-- CONTENT -->
        <div id="faq-content" style="
            flex: 1; overflow-y: auto; padding: 1.5rem;
        ">
            <p style="text-align: center; color: #1a1a2e; font-weight: 600;">👋 Bonjour!</p>
            <p style="text-align: center; color: #666; font-size: 0.9rem; margin-bottom: 1rem;">Comment puis-je vous aider?</p>

            <!-- SEARCH -->
            <div style="display: flex; gap: 0.5rem; margin-bottom: 1.5rem;">
                <input id="faq-search" type="text" placeholder="🔍 Chercher..." style="
                    flex: 1; padding: 10px; border: 2px solid #ddd; border-radius: 6px;
                ">
                <button id="faq-search-btn" style="
                    padding: 10px 15px; background: #667eea; color: white;
                    border: none; border-radius: 6px; cursor: pointer; font-weight: 600;
                ">OK</button>
            </div>

            <!-- POPULAR CATEGORIES (ONLY) -->
            <h4 style="margin: 0 0 1rem 0; color: #1a1a2e; font-size: 0.9rem;">📌 Catégories Populaires:</h4>
            <div id="faq-popular-categories" style="display: flex; flex-direction: column; gap: 0.8rem;">
                <p style="color: #999; text-align: center;">Chargement...</p>
            </div>

            <!-- ALL CATEGORIES BUTTON -->
            <button id="faq-show-all-btn" style="
                margin-top: 1rem; padding: 10px; background: #f5f5f5;
                border: 2px dashed #ddd; border-radius: 6px; cursor: pointer;
                color: #667eea; font-weight: 600; width: 100%;
                transition: all 0.3s;
            ">📂 Voir toutes les catégories</button>
        </div>

        <!-- FOOTER -->
        <div style="
            background: #f5f5f5; padding: 1rem; border-top: 1px solid #ddd;
            text-align: center; font-size: 0.8rem; color: #666;
        ">
            Besoin d'aide? <a href="mailto:info@fragranceboutique.com" style="color: #667eea; text-decoration: none;">Contactez-nous</a>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const btn = document.getElementById('faq-btn');
    const widget = document.getElementById('faq-widget');
    const closeBtn = document.getElementById('faq-close');
    const searchInput = document.getElementById('faq-search');
    const searchBtn = document.getElementById('faq-search-btn');
    const popularCategoriesDiv = document.getElementById('faq-popular-categories');
    const showAllBtn = document.getElementById('faq-show-all-btn');
    const contentDiv = document.getElementById('faq-content');

    // POPULAR CATEGORIES (select these)
    const POPULAR_CATEGORIES = ['Livraison', 'Paiement', 'Produits', 'Retours', 'Compte'];

    // TOGGLE WIDGET
    btn.addEventListener('click', function() {
        if (widget.style.display === 'none') {
            widget.style.display = 'flex';
            searchInput.focus();
        } else {
            widget.style.display = 'none';
        }
    });

    // CLOSE WIDGET
    closeBtn.addEventListener('click', function() {
        widget.style.display = 'none';
    });

    // LOAD ONLY POPULAR CATEGORIES
    function loadPopularCategories() {
        fetch('faq_api.php?action=get_categories')
            .then(r => r.json())
            .then(d => {
                if (d.success) {
                    popularCategoriesDiv.innerHTML = '';
                    
                    // Filter only popular categories
                    const popular = d.data.filter(cat => POPULAR_CATEGORIES.includes(cat));
                    
                    popular.forEach(cat => {
                        const categoryBtn = document.createElement('button');
                        categoryBtn.textContent = '📁 ' + cat;
                        categoryBtn.style.cssText = `
                            padding: 10px; background: #f5f5f5; border: 2px solid #ddd;
                            border-radius: 6px; cursor: pointer; font-weight: 500;
                            transition: all 0.3s;
                        `;
                        categoryBtn.onmouseover = () => {
                            categoryBtn.style.background = '#667eea';
                            categoryBtn.style.color = 'white';
                            categoryBtn.style.borderColor = '#667eea';
                        };
                        categoryBtn.onmouseout = () => {
                            categoryBtn.style.background = '#f5f5f5';
                            categoryBtn.style.color = 'black';
                            categoryBtn.style.borderColor = '#ddd';
                        };
                        categoryBtn.addEventListener('click', () => loadCategory(cat));
                        popularCategoriesDiv.appendChild(categoryBtn);
                    });

                    // Restore the content area to show search and categories
                    contentDiv.innerHTML = `
                        <p style="text-align: center; color: #1a1a2e; font-weight: 600;">👋 Bonjour!</p>
                        <p style="text-align: center; color: #666; font-size: 0.9rem; margin-bottom: 1rem;">Comment puis-je vous aider?</p>
                        <div style="display: flex; gap: 0.5rem; margin-bottom: 1.5rem;">
                            <input id="faq-search" type="text" placeholder="🔍 Chercher..." style="
                                flex: 1; padding: 10px; border: 2px solid #ddd; border-radius: 6px;
                            ">
                            <button id="faq-search-btn" style="
                                padding: 10px 15px; background: #667eea; color: white;
                                border: none; border-radius: 6px; cursor: pointer; font-weight: 600;
                            ">OK</button>
                        </div>
                        <h4 style="margin: 0 0 1rem 0; color: #1a1a2e; font-size: 0.9rem;">📌 Catégories Populaires:</h4>
                        <div id="faq-popular-categories-inner"></div>
                        <button id="faq-show-all-btn" style="
                            margin-top: 1rem; padding: 10px; background: #f5f5f5;
                            border: 2px dashed #ddd; border-radius: 6px; cursor: pointer;
                            color: #667eea; font-weight: 600; width: 100%;
                            transition: all 0.3s;
                        ">📂 Voir toutes les catégories</button>
                    `;

                    // Re-add categories to new div
                    const innerDiv = document.getElementById('faq-popular-categories-inner');
                    popular.forEach(cat => {
                        const categoryBtn = document.createElement('button');
                        categoryBtn.textContent = '📁 ' + cat;
                        categoryBtn.style.cssText = `
                            padding: 10px; background: #f5f5f5; border: 2px solid #ddd;
                            border-radius: 6px; cursor: pointer; font-weight: 500;
                            margin-bottom: 0.8rem; width: 100%;
                            transition: all 0.3s;
                        `;
                        categoryBtn.onmouseover = () => {
                            categoryBtn.style.background = '#667eea';
                            categoryBtn.style.color = 'white';
                            categoryBtn.style.borderColor = '#667eea';
                        };
                        categoryBtn.onmouseout = () => {
                            categoryBtn.style.background = '#f5f5f5';
                            categoryBtn.style.color = 'black';
                            categoryBtn.style.borderColor = '#ddd';
                        };
                        categoryBtn.addEventListener('click', () => loadCategory(cat));
                        innerDiv.appendChild(categoryBtn);
                    });

                    // Re-attach search and show all listeners
                    document.getElementById('faq-search-btn').addEventListener('click', () => searchFAQs(document.getElementById('faq-search').value));
                    document.getElementById('faq-search').addEventListener('keypress', (e) => {
                        if (e.key === 'Enter') searchFAQs(e.target.value);
                    });
                    document.getElementById('faq-show-all-btn').addEventListener('click', showAllCategories);
                }
            })
            .catch(e => {
                console.error('Error:', e);
            });
    }

    // SHOW ALL CATEGORIES
    function showAllCategories() {
        fetch('faq_api.php?action=get_categories')
            .then(r => r.json())
            .then(d => {
                if (d.success) {
                    let html = `
                        <button id="faq-back-btn" style="
                            background: none; border: none; color: #667eea;
                            cursor: pointer; font-weight: 600; margin-bottom: 1rem;
                            padding: 0; font-size: 1rem;
                        ">← Retour</button>
                        <h4 style="margin: 0 0 1rem 0;">📂 Toutes les catégories</h4>
                    `;
                    
                    d.data.forEach(cat => {
                        html += `
                            <button class="faq-all-cat-btn" data-category="${cat}" style="
                                padding: 10px; background: #f5f5f5; border: 2px solid #ddd;
                                border-radius: 6px; cursor: pointer; font-weight: 500;
                                margin-bottom: 0.8rem; width: 100%;
                                transition: all 0.3s;
                            ">
                                📁 ${cat}
                            </button>
                        `;
                    });
                    
                    contentDiv.innerHTML = html;

                    // Add event listeners
                    document.getElementById('faq-back-btn').addEventListener('click', loadPopularCategories);
                    document.querySelectorAll('.faq-all-cat-btn').forEach(btn => {
                        btn.addEventListener('click', function() {
                            btn.style.background = '#667eea';
                            btn.style.color = 'white';
                            btn.style.borderColor = '#667eea';
                            setTimeout(() => {
                                loadCategory(this.getAttribute('data-category'));
                            }, 100);
                        });
                        btn.onmouseover = function() {
                            this.style.background = '#667eea';
                            this.style.color = 'white';
                            this.style.borderColor = '#667eea';
                        };
                        btn.onmouseout = function() {
                            this.style.background = '#f5f5f5';
                            this.style.color = 'black';
                            this.style.borderColor = '#ddd';
                        };
                    });
                }
            })
            .catch(e => console.error('Error:', e));
    }

    // SEARCH
    function searchFAQs(query) {
        if (!query.trim()) return;
        fetch(`faq_api.php?action=search&q=${encodeURIComponent(query)}`)
            .then(r => r.json())
            .then(d => {
                if (d.success && d.data.length > 0) {
                    displayFAQs(d.data, 'Résultats de recherche');
                } else {
                    contentDiv.innerHTML = '<p style="text-align: center; color: #999;">Aucun résultat trouvé</p>';
                }
            })
            .catch(e => console.error('Error:', e));
    }

    function loadCategory(category) {
        fetch(`faq_api.php?action=get_by_category&category=${encodeURIComponent(category)}`)
            .then(r => r.json())
            .then(d => {
                if (d.success) {
                    displayFAQs(d.data, category);
                }
            })
            .catch(e => console.error('Error:', e));
    }

    function displayFAQs(faqs, title = 'Résultats') {
        let html = `
            <button id="faq-back-btn" style="
                background: none; border: none; color: #667eea;
                cursor: pointer; font-weight: 600; margin-bottom: 1rem;
                padding: 0; font-size: 1rem;
            ">← Retour</button>
            <h4 style="margin: 0 0 1rem 0;">${title}</h4>
        `;
        
        faqs.forEach(faq => {
            html += `
                <div class="faq-item" data-faq-id="${faq.id}" style="
                    padding: 10px; background: #f9f9f9;
                    border-left: 4px solid #667eea; border-radius: 4px;
                    cursor: pointer; margin-bottom: 0.8rem;
                    transition: all 0.3s;
                ">
                    <p style="margin: 0; font-weight: 600; color: #1a1a2e; font-size: 0.95rem;">${faq.question}</p>
                    <p style="margin: 3px 0 0 0; font-size: 0.75rem; color: #999;">${faq.category}</p>
                </div>
            `;
        });
        
        contentDiv.innerHTML = html;

        // Add back button listener
        document.getElementById('faq-back-btn').addEventListener('click', loadPopularCategories);

        // Add FAQ item listeners
        document.querySelectorAll('.faq-item').forEach(item => {
            item.addEventListener('mouseover', function() {
                this.style.background = '#eff2ff';
                this.style.transform = 'translateX(4px)';
            });
            item.addEventListener('mouseout', function() {
                this.style.background = '#f9f9f9';
                this.style.transform = 'translateX(0)';
            });
            item.addEventListener('click', () => {
                showDetail(parseInt(item.getAttribute('data-faq-id')));
            });
        });
    }

    function showDetail(id) {
        fetch(`faq_api.php?action=get_by_id&id=${id}`)
            .then(r => r.json())
            .then(d => {
                if (d.success) {
                    const faq = d.data;
                    let html = `
                        <button id="faq-back-btn" style="
                            background: none; border: none; color: #667eea;
                            cursor: pointer; font-weight: 600; margin-bottom: 1rem;
                            padding: 0; font-size: 1rem;
                        ">← Retour</button>
                        <h5 style="color: #667eea; margin: 0 0 0.8rem 0; line-height: 1.4;">${faq.question}</h5>
                        <p style="color: #666; margin: 0; line-height: 1.6; font-size: 0.95rem;">${faq.answer}</p>
                    `;
                    contentDiv.innerHTML = html;

                    // Add back button listener
                    document.getElementById('faq-back-btn').addEventListener('click', loadPopularCategories);
                }
            })
            .catch(e => console.error('Error:', e));
    }

    // Initialize
    loadPopularCategories();
    showAllBtn.addEventListener('click', showAllCategories);
});
</script>