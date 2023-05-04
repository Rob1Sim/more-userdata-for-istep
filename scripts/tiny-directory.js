
//On récupère le tableau
const rows = document.querySelectorAll('tbody tr');
//Pour chaque ligne on ajoute un élément caché qui contient le document
rows.forEach((row) => {
    const userId = row.querySelector('[data-id]').getAttribute('data-id');
    const detailsRow = document.createElement('tr');
    detailsRow.classList.add('details');

    const detailsCell = document.createElement('td');
    detailsCell.setAttribute('colspan', '3');

    //Récupération du lien vers la page de profile
    const linkToProfilePage = row.querySelector(`#login-${userId}`).textContent;


    //Récupération de l'image de profile caché
    const profilePicture = document.querySelector(`#pp-${userId}>img`)
    const ppCopy = profilePicture.cloneNode(true);

    //Récupération des informations déjà affichés
    const displayName = "Nom : "+row.querySelector(`.name-${userId}`).textContent;
    const email = "Email : "+row.querySelector(`.email-${userId}`).textContent;
    const phone = "Téléphone : "+row.querySelector(`.phone-${userId}`).textContent;
    const office = "Bureau : "+row.querySelector(`.office-${userId}`).textContent;
    const tower = "Tour : "+row.querySelector(`.tower-${userId}`).textContent;
    const campus = "Campus : "+row.querySelector(`.campus-${userId}`).textContent;

    //Créations des élements affiché dans le bloc caché
    const displayNameP = document.createElement('p');
    displayNameP.textContent = displayName;

    const emailP = document.createElement('p');
    emailP.textContent = email;

    const phoneP = document.createElement('p');
    phoneP.textContent = phone;

    const campusP = document.createElement('p');
    campusP.textContent = campus;
    const towerP = document.createElement('p');
    towerP.textContent = tower;
    const officeP = document.createElement('p');
    officeP.textContent = office;

    const linkToProfilePageA = document.createElement('a');
    linkToProfilePageA.textContent = "Page de profile";
    linkToProfilePageA.href = linkToProfilePage

    const detailsContent = document.createElement('div');
    detailsContent.classList.add('detail-div-flex');

    const imgDiv = document.createElement('div');
    imgDiv.appendChild(ppCopy);

    const detailsTextContent = document.createElement('div')
    detailsTextContent.classList.add('detail-div-flex-text');

    detailsTextContent.appendChild(imgDiv);
    detailsTextContent.appendChild(displayNameP);
    detailsTextContent.appendChild(emailP);
    detailsTextContent.appendChild(phoneP);

    const detailsCoordonates = document.createElement('div')
    detailsCoordonates.classList.add('detail-div-flex-text');
    detailsCoordonates.appendChild(campusP);
    detailsCoordonates.appendChild(towerP);
    detailsCoordonates.appendChild(officeP);

    detailsContent.appendChild(detailsTextContent);
    detailsContent.appendChild(detailsCoordonates);
    detailsContent.appendChild(linkToProfilePageA);

    detailsCell.appendChild(detailsContent);
    detailsRow.appendChild(detailsCell);
    row.insertAdjacentElement('afterend', detailsRow);

    //On écoute le clique sur chaque ligne, puis on affiche si l'élément est cliqué
    row.addEventListener('click', () => {
        detailsRow.classList.toggle('active');
    });
    row.addEventListener('keyup', (e) => {
        if(e.code === 'Enter' || e.code === 'Space'){
            detailsRow.classList.toggle('active');
        }
    });
});
//Champs de recherche
const searchInputMembers = document.getElementById('search-input-members');
searchInputMembers.addEventListener('input', () => {
    const filterValue = searchInputMembers.value.trim().toLowerCase();
    rows.forEach(row => {
        const userId = row.querySelector('[data-id]').getAttribute('data-id');
        const displayName = row.querySelector(`.name-${userId}`).textContent.trim().toLowerCase();
        const email = row.querySelector(`.email-${userId}`).textContent.trim().toLowerCase();
        if (displayName.includes(filterValue) || email.includes(filterValue)) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
});
