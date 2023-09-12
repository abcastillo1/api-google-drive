class Repositorio {    

    constructor() {
        this.key='1TE512vI9m2SpfILntpPBzxKWqDaofBtM';
        this.carpetaPrincipal=this.key;
        this.listadoDirectorios=[];        
    }

    async listarCarpeta() {
        try {
            mostrarMensaje(true);
            const parametros = new FormData();
            parametros.append("peticion", "listarCarpeta");
            parametros.append("directorio", this.carpetaPrincipal);
            const resultado = await fetch("repositorio.php", {
                method: 'POST',
                body: parametros
            });
            const datos = await resultado.json();
            this.listadoDirectorios = datos.contenido;
            mostrarElementos();
            mostrarMensaje(false);
            return datos;
        } catch (error) {
            mostrarMensaje(false);
            return { datos: false, error: error };
        }
    }


    async crearCarpeta(nombre) {
        try {
            mostrarMensaje(true);
            let parametros = new FormData();
            parametros.append("peticion", "crearCarpeta");
            parametros.append("nombre", nombre);
            parametros.append("directorio", this.carpetaPrincipal);
            const resultado = await fetch("repositorio.php", {
                method: 'POST',
                body: parametros
            });
            const datos = resultado.json();
            mostrarMensaje(false);
            return datos;
        } catch (error) {
            mostrarMensaje(false);
            return {"datos":false,"error":error}
        }
    }


    async crearArchivo(archivo) {
        try {
            let parametros = new FormData();
            parametros.append("peticion", "crearArchivo");                    
            parametros.append("archivo", archivo);
            parametros.append("carpeta", this.carpetaPrincipal);
            const resultado = await fetch("repositorio.php", {
                method: 'POST',
                body: parametros
            });
            const datos = resultado.json();
            return datos;
        } catch (error) {
            return {"datos":false,"error":error}
        }
    }

    async eliminarArchivo(archivo) {
        try {
            let parametros = new FormData();
            parametros.append("peticion", "eliminarArchivo");                    
            parametros.append("archivo", archivo);
            const resultado = await fetch("repositorio.php", {
                method: 'POST',
                body: parametros
            });
            const datos = resultado.json();
            return datos;
        } catch (error) {
            return {"datos":false,"error":error}
        }
    }



    async eliminarCarpeta(directorio) {
        try {
            let parametros = new FormData();
            parametros.append("peticion", "eliminarCarpeta");                    
            parametros.append("directorio", directorio);
            const resultado = await fetch("repositorio.php", {
                method: 'POST',
                body: parametros
            });
            const datos = resultado.json();
            return datos;
        } catch (error) {
            return {"datos":false,"error":error}
        }
    }
}    

const repositorio = new Repositorio();

document.addEventListener('DOMContentLoaded', async () => {
    
    await repositorio.listarCarpeta();

    document.querySelector('#inicio').addEventListener('click', function () {
        repositorio.carpetaPrincipal = repositorio.key;        
        repositorio.listarCarpeta();        
    }); 
    
    
    document.querySelector(".guardar-archivo").addEventListener("click", async () => {
        const archivoSeleccionado = document.getElementById("cargar-archivo").files[0];    
        if (archivoSeleccionado) {
            let respuesta = await repositorio.crearArchivo(archivoSeleccionado);
            if(respuesta){
                alert("Archivo " + archivoSeleccionado.name +" Subido Correctamente");
                document.getElementById("cargar-archivo").value=null;
            }
            repositorio.listarCarpeta();
        } else {
            alert("Ningún archivo seleccionado");
        }
    });

    
    document.querySelector(".crear-carpeta").addEventListener("click", async () => {
        const nombre = prompt("Ingrese el nombre de la Carpeta:");      
        if (nombre !== null && nombre !== "") {            
            let respuesta = await repositorio.crearCarpeta(nombre);
            if(respuesta){
                alert("Carpeta " + nombre +" creada correctamente");
            }
            repositorio.listarCarpeta();
        } else {
          alert("No ingresaste un nombre válido. Por favor, inténtalo de nuevo.");
        }
      });
});


/*DIBUJAR LAS CARPETAS Y ARCHIVOS*/
function mostrarElementos() {
    const contenedorElementos = document.getElementById('contenedor-elementos');
    contenedorElementos.innerHTML="";
    repositorio.listadoDirectorios.forEach((elemento) => {
        const elementoDOM = document.createElement('div');
        elementoDOM.className = elemento.tipo === 'carpeta' ? 'folder' : 'file';
        elementoDOM.style.cursor="pointer"

        const elementoIcono = document.createElement('img');
        elementoIcono.src = elemento.tipo === 'carpeta' ? 'img/carpeta.png' : 'img/archivo.png';
        elementoIcono.style.width='25px';
        elementoIcono.style.marginLeft='25px';
        const elementoNombre = document.createElement('div');
        elementoNombre.className = 'nombre';
        elementoNombre.textContent = elemento.nombre;

        elementoDOM.appendChild(elementoIcono);
        elementoDOM.appendChild(elementoNombre);

        if (elemento.tipo === 'carpeta') {
            elementoDOM.ondblclick = () => {
                console.log('Doble clic: ' + elemento.nombre);
                repositorio.carpetaPrincipal=elemento.id
                repositorio.listarCarpeta();
            };

            const botonEliminar = document.createElement('button');
            botonEliminar.className = 'delete-button';
            botonEliminar.textContent = 'Eliminar';
            botonEliminar.onclick = async  () => {
                await repositorio.eliminarCarpeta(elemento.id);
                alert("La Carpeta "+ elemento.nombre+" fue eliminada");
                repositorio.listarCarpeta();
            };

            elementoDOM.appendChild(botonEliminar);
        } else {
            const botonEliminar = document.createElement('button');
            botonEliminar.className = 'delete-button';
            botonEliminar.textContent = 'Eliminar';
            botonEliminar.onclick = async() => {
                await repositorio.eliminarArchivo(elemento.id);
                alert("El archivo "+ elemento.nombre+" fue eliminado");
                repositorio.listarCarpeta();
            };

            elementoDOM.appendChild(botonEliminar);
        }
        
        contenedorElementos.appendChild(elementoDOM);
    });
}

function mostrarMensaje(estado){
    let mensaje = estado?"Cargando...":"";
    const texto = document.getElementById("texto-cargando");
    texto.textContent=mensaje;
    texto.style.color="red";
    texto.style.fontWeight=700;
}