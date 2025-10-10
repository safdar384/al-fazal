function toggleSidebar() {
    document.getElementById('sidebar').classList.toggle('collapsed');
    document.querySelector('.main-area').classList.toggle('collapsed');
}

function toggleMenu(el) {
    document.querySelectorAll('.menu-group').forEach(group => {
        if (group !== el.parentElement) {
            group.classList.remove('open');
        }
    });
    el.parentElement.classList.toggle('open');
}
