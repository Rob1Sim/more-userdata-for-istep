/**
 * Utilisée pour filtrer les données d'une table HTML en fonction de la colonne spécifiée.
 * Elle récupère les éléments HTML de la table, puis parcourt chaque ligne de la table pour extraire les données de la colonne spécifiée.
 * @param column
 */
function tableFilter(column) {
    // Récupération des éléments HTML
    const table = document.querySelector('#istep-users-list');
    const tbody = table.querySelector('tbody');

    // Récupération des données
    const rows = tbody.querySelectorAll('tr');
    let data = [];

    rows.forEach(function(row) {
        let rowData = {};
        let cells = row.querySelectorAll('td');
        const dropdownInRow = row.querySelector('select');

        cells.forEach(function(cell, index) {
            if (dropdownInRow && index === 3){
                rowData[index] = dropdownInRow.querySelector('option:checked').textContent
            }else{
                rowData[index] = cell.textContent.trim();
            }
        });

        data.push(rowData);
    });

    // Tri des données
    data.sort(function(a, b) {
        if (a[column] < b[column]) {
            return -1;
        }
        if (a[column] > b[column]) {
            return 1;
        }
        return 0;
    });

    // Réécriture de la table
    tbody.innerHTML = '';

    data.forEach(function(rowData) {
        const row = document.createElement('tr');

        for (let key in rowData) {
            let cell = document.createElement('td');
            cell.textContent = rowData[key];
            row.appendChild(cell);
        }

        tbody.appendChild(row);
    });
}

/**
 * Utilisée pour rechercher et filtrer les données d'une table HTML en fonction d'une chaîne de caractères spécifiée.
 * Elle récupère les éléments HTML de la table, parcourt chaque ligne de la table,
 * puis vérifie si la chaîne de caractères recherchée est présente dans l'une des cellules de la ligne.
 * Les lignes qui correspondent à la recherche sont affichées, tandis que les autres lignes sont masquées.
 * @param searchString
 */
function searchInTable(searchString) {
    // Récupération des éléments HTML
    const table = document.querySelector('#istep-users-list');
    const tbody = table.querySelector('tbody');

    // Récupération des données
    const rows = tbody.querySelectorAll('tr');

    // Filtrage des données
    rows.forEach(function(row) {
        let found = false;
        let cells = row.querySelectorAll('td');

        cells.forEach(function(cell) {
            if (cell.textContent.toLowerCase().indexOf(searchString.toLowerCase()) !== -1) {
                found = true;
            }
        });

        if (found) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
}



const dropdown = document.querySelector('#dropdown-colonne');
try{
    dropdown.addEventListener('change', function() {
        tableFilter(dropdown.selectedIndex);
    });
}catch (e){}

try {
    const search = document.querySelector('#search');
    search.addEventListener('input', function() {
        if (search.value === '') {
            // Si la barre de recherche est vide, on réinitialise la table
            tableFilter(dropdown.selectedIndex);
        } else {
            searchInTable(search.value);
        }
    });
}catch (e){}

