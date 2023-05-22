
//formulaire de création de l'utilisateur
const submit_button = document.getElementById("create-user-submit-btn");
const form = document.getElementById("create-user-istep-form");
let showPassword = false;

submit_button.addEventListener('click',(event)=>{
    const elements = document.querySelectorAll('.user-create-error');
    elements.forEach((e)=>{
        e.remove();
    })

    event.preventDefault();
    const name = document.getElementById("name");
    const lastName = document.getElementById("last_name");
    const login = document.getElementById("login");
    const email = document.getElementById("email");
    const phone = document.getElementById("phoneNumber");
    const password = document.getElementById("password");
    const office = document.getElementById("office");
    const job = document.getElementById("job");
    const rank = document.getElementById("teamRank");
    const campus = document.getElementById("campus");
    const employer = document.getElementById("employer");
    const mailCase = document.getElementById("mailCase");
    const tower = document.getElementsByName("tourBureau");
    const teams = document.getElementsByName("teams[]");

    if(name.value === "" || name.value === null|| name.value.length > 255){
        addErrorMessage("Prénom incorrect",document.querySelector('#name').parentNode);
        return;
    }
    if(lastName.value === "" || lastName.value === null|| lastName.value.length > 255){
        addErrorMessage("Nom incorrect",document.querySelector('#last_name').parentNode);
        return;
    }
    if(login.value === "" || login.value === null|| login.value.length > 255){
        addErrorMessage("Login incorrect",document.querySelector('#login').parentNode);
        return;
    }
    if(email.value === "" || email.value === null || !isValidEmail(email.value)|| email.value.length > 255){
        addErrorMessage("Email incorrect",document.querySelector('#email').parentNode);
        return;
    }
    if(password.value === "" || password.value === null){
        addErrorMessage("Mot de passe non valide",document.querySelector('#password').parentNode);
        return;
    }
    if(office.value === "" || office.value === null || office.value.length>4){
        addErrorMessage("Bureau incorrect",document.querySelector('#office').parentNode);
        return;
    }
    if(job.value === "" || job.value === null || job.value.length > 255){
        addErrorMessage("Fonction incorrect",document.querySelector('#job').parentNode);
        return;
    }
    if(rank.value === "" || rank.value === null ||rank.value.length > 255){
        addErrorMessage("Rang incorrect",document.querySelector('#teamRank').parentNode);
        return;
    }
    if(campus.value === "" || campus.value === null|| campus.value.length > 255){
        addErrorMessage("Campus incorrect",document.querySelector('#campus').parentNode);
        return;
    }
    if(employer.value === "" || employer.value === null|| employer.value.length > 255){
        addErrorMessage("Employeur incorrect",document.querySelector('#employer').parentNode);
        return;
    }
    if(mailCase.value === "" || mailCase.value === null|| mailCase.value.length > 10){
        addErrorMessage("Case courrier incorrect",document.querySelector('#mailCase').parentNode);
        return;
    }

    if (!isOnlyOneRadioButtonSelected(tower)){
        addErrorMessage("Tour de bureau incorrect",document.querySelector('#tower').parentNode);
        return;
    }

    //Vérification qu'au moins une équipe est sélectionné
    let thereIsAtLeastOneTeam = false;
    teams.forEach((input)=>{
        if (input.checked){
            thereIsAtLeastOneTeam = true;
        }
    })

    if (!thereIsAtLeastOneTeam){
        addErrorMessage("Equipe incorrect",document.querySelector('#c'));
        return;
    }

    if(!validatePhoneNumber(phone.value)){
        addErrorMessage("Numéro de téléphone incorrect",document.querySelector('#phoneNumber').parentNode);
        return;
    }
    form.submit(); // Soumet le formulaire si tout est valide
})

//Formulaire de mis à jour de l'utilisateur
const updateForm = document.getElementById("update-user-profile-istep");
const updateFormBtn = document.getElementById("update-user-submit-btn");
console.log(updateFormBtn)
console.log("TAMERE")
updateFormBtn.addEventListener('click',(event)=>{
    event.preventDefault();
    console.log("IMHERE");
    const office = document.getElementById("office");
    const phone = document.getElementById("phoneNumber");
    const campus = document.getElementById("campus");
    const tower = document.getElementsByName("tourBureau");

    if(office.value === "" || office.value === null || office.value.length>4){
        addErrorMessage("Bureau incorrect",document.querySelector('#office').parentNode);
        return;
    }
    if(campus.value === "" || campus.value === null|| campus.value.length > 255){
        addErrorMessage("Campus incorrect",document.querySelector('#campus').parentNode);
        return;
    }
    if(!validatePhoneNumber(phone.value)){
        addErrorMessage("Numéro de téléphone incorrect",document.querySelector('#phoneNumber').parentNode);
        return;
    }
    if (!isOnlyOneRadioButtonSelected(tower)){
        addErrorMessage("Tour de bureau incorrect",document.querySelector('#tower').parentNode);
        return;
    }
    updateForm.submit();

})

//Btn de génération et affichage de mot de passe
    document.getElementById("random-pws").addEventListener("click",()=>{
        const password = document.getElementById("password");
        password.value = generatePassword(12);
    })
    document.getElementById("show-password").addEventListener("click",()=>{
        showPassword = !showPassword;
        const password = document.getElementById("password");
        if(showPassword){
            password.type = "text";
            document.getElementById("show-password").text = "Masquer le mot de passe";
        }else{
            password.type = "password";
            document.getElementById("show-password").text = "Masquer le mot de passe";
        }
    })
    /**
     * Génère un mot de passe aléatoire
     * @param length
     * @returns {string}
     */
    function generatePassword(length) {
        const chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789!@#$%^&*()_+";

        let password = "";

        for (let i = 0; i < length; i++) {
            const randomChar = chars[Math.floor(Math.random() * chars.length)];
            password += randomChar;
        }

        return password;
    }


/**
 * Vérifie si le numéro de téléphone entré est valide
 * @param phoneNumber
 * @returns {boolean}
 */

function validatePhoneNumber(phoneNumber) {
    const phoneRegex = /^[\+]?[(]?[0-9]{3}[)]?[-\s\.]?[0-9]{3}[-\s\.]?[0-9]{4,6}$/im;
    return phoneRegex.test(phoneNumber);
}

/**
 * Affiche un message le message passé en paramètre sous l'élément en question
 * @param errorText
 * @param parentSelector
 */
function addErrorMessage(errorText,parentSelector){
    const errorMessage = document.createElement("div");
    errorMessage.classList.add("user-create-error");
    errorMessage.appendChild(document.createTextNode(errorText));
    parentSelector.appendChild(errorMessage);
}

/**
 * Vérifie si une email est valide
 * @param email
 * @returns {boolean}
 */
function isValidEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
}

/**
 * Vérifie que un unique bouton est préssé
 * @returns {boolean}
 */
function isOnlyOneRadioButtonSelected(radioButtons) {
    let checked = false;
    for (let i = 0; i < radioButtons.length; i++) {
        if (radioButtons[i].checked) {
            if (checked) {
                // Si un autre bouton radio est déjà coché, retourner false
                return false;
            }
            checked = true;
        }
    }
    // Retourner true si un et seulement un bouton radio est coché
    return checked;
}