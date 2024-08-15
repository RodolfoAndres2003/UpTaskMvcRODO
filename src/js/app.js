const mobileMenuBtn = document.querySelector('#mobile-menu');
const cerrarMenuBtn = document.querySelector('#cerrar-menu');
const sidebar = document.querySelector('.sidebar');
if(mobileMenuBtn){
    mobileMenuBtn.addEventListener('click', function(){
        sidebar.classList.add('mostrar');
    });
}
if(cerrarMenuBtn){
    cerrarMenuBtn.addEventListener('click', function(){
        // 
        sidebar.classList.add('ocultar');
        setTimeout(() => {
            sidebar.classList.remove('mostrar');
            sidebar.classList.remove('ocultar');
        }, 500);
    });
}
// ELIMINA LA CLASE DE MOSTRAR EN UN TAMAÑO DE TABLET

const anchoPantalle = document.body.clientWidth;

window.addEventListener('resize', function(){
    const anchoPantalle = document.body.clientWidth;
    
    if(anchoPantalle >= 768){
        sidebar.classList.remove('mostrar');
    }
    
});