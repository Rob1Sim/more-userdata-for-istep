const submit_button = document.getElementById("create-user-submit-btn");
const form = document.getElementById("create-user-istep-form");
let showPassword = false;
//Vérificateur de formulaire
submit_button.addEventListener('click',(event)=>{
    const elements = document.querySelectorAll('.user-create-error');


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
    const team = document.getElementById("team");

    if(name.value === "" || name.value === null){
        addErrorMessage("Prénom incorrect",document.querySelector('#name').parentNode);
        return;
    }
    if(lastName.value === "" || lastName.value === null){
        addErrorMessage("Nom incorrect",document.querySelector('#last_name').parentNode);
        return;
    }
    if(login.value === "" || login.value === null){
        addErrorMessage("Login incorrect",document.querySelector('#login').parentNode);
        return;
    }
    if(email.value === "" || email.value === null || !isValidEmail(email.value)){
        addErrorMessage("Email incorrect",document.querySelector('#email').parentNode);
        return;
    }
    if(password.value === "" || password.value === null){
        addErrorMessage("Mot de passe non valide",document.querySelector('#password').parentNode);
        return;
    }
    if(office.value === "" || office.value === null){
        addErrorMessage("Bureau incorrect",document.querySelector('#office').parentNode);
        return;
    }
    if(job.value === "" || job.value === null){
        addErrorMessage("Fonction incorrect",document.querySelector('#job').parentNode);
        return;
    }
    if(rank.value === "" || rank.value === null){
        addErrorMessage("Rang incorrect",document.querySelector('#teamRank').parentNode);
        return;
    }
    if(campus.value === "" || campus.value === null){
        addErrorMessage("Campus incorrect",document.querySelector('#campus').parentNode);
        return;
    }
    if(employer.value === "" || employer.value === null){
        addErrorMessage("Employeur incorrect",document.querySelector('#employer').parentNode);
        return;
    }
    if(mailCase.value === "" || mailCase.value === null){
        addErrorMessage("Case courrier incorrect",document.querySelector('#mailCase').parentNode);
        return;
    }
    if (tower.length !== 1){
        addErrorMessage("Tour de bureau incorrect",document.querySelector('#tower').parentNode);
        return;
    }

    if(team.value ==="" || team.value === null){
        addErrorMessage("Equipe incorrect",document.querySelector('#team-label').parentNode);
        return;
    }

    if(!validatePhoneNumber(phone.value)){
        addErrorMessage("Numéro de téléphone incorrect",document.querySelector('#phoneNumber').parentNode);
        return;
    }
    form.submit(); // Soumet le formulaire si tout est valide
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