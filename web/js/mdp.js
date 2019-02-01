function switchInputType(id, el) {
    var x = document.getElementById(id);
    // switch type
    x.type = x.type === "password" ? "text" : "password";
    // switch classes
    el.classList.toggle("fa-eye");
    el.classList.toggle("fa-eye-slash");
    // set focus
    x.focus();
}
