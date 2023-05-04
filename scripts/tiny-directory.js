
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
    const phone = "phone"//row.querySelector(`.phone-${userId}`).textContent;
    const position = "position"//row.querySelector(`.position-${userId}`).textContent;

    //Créations des élements affiché dans le bloc caché
    const displayNameP = document.createElement('p');
    displayNameP.textContent = displayName;

    const emailP = document.createElement('p');
    emailP.textContent = email;

    const phoneP = document.createElement('p');
    phoneP.textContent = phone;

    const positionP = document.createElement('p');
    positionP.textContent = position;

    const linkToProfilePageA = document.createElement('a');
    linkToProfilePageA.textContent = "Page de profile";
    linkToProfilePageA.href = linkToProfilePage

    const detailsContent = document.createElement('div');
    detailsContent.classList.add('detail-div-flex');
    detailsContent.appendChild(ppCopy);

    const detailsTextContent = document.createElement('div')
    detailsTextContent.classList.add('detail-div-flex-text');

    detailsTextContent.appendChild(displayNameP);
    detailsTextContent.appendChild(emailP);
    //detailsTextContent.appendChild(phone);
    //detailsTextContent.appendChild(position);
    detailsTextContent.appendChild(linkToProfilePageA);

    detailsContent.appendChild(detailsTextContent);

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
const searchInput = document.getElementById('search-input');
searchInput.addEventListener('input', () => {
    const filterValue = searchInput.value.trim().toLowerCase();
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
