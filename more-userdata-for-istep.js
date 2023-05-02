const submit_button = document.getElementById("create-user-submit-btn");
const form = document.getElementById("create-user-istep-form");
console.log(document.getElementById("phoneNumber"))
submit_button.addEventListener('click',(event)=>{
    event.preventDefault();
    const phone = document.getElementById("phoneNumber");
    if(!validatePhoneNumber(phone.value)){
        const errorMessage = document.createElement("div");
        errorMessage.classList.add("user-create-error");
        errorMessage.appendChild(document.createTextNode("Numéro de téléphone incorrect"));
        document.getElementById("phoneParent").appendChild(errorMessage);
        console.log("OH j'ai le droit de vivre un peu"+validatePhoneNumber(phone.value))

        return;
    }
    form.submit(); // Soumet le formulaire si tout est valide
})

/**
 * Vérifie si le numéro de téléphone entré est valide
 * @param phoneNumber
 * @returns {boolean}
 */

function validatePhoneNumber(phoneNumber) {
    const phoneRegex = /^[\+]?[(]?[0-9]{3}[)]?[-\s\.]?[0-9]{3}[-\s\.]?[0-9]{4,6}$/im;
    console.log("Tarlouse "+phoneNumber)
    return phoneRegex.test(phoneNumber);
}