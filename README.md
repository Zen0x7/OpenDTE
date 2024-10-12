# OpenDTE

<p align="center">
<a href="https://github.com/Zen0x7/OpenDTE/actions/workflows/build.yml"><img src="https://github.com/Zen0x7/OpenDTE/actions/workflows/build.yml/badge.svg?branch=master"/></a>
<a href="https://github.com/Zen0x7/OpenDTE/actions/workflows/container.yml"><img src="https://github.com/Zen0x7/OpenDTE/actions/workflows/container.yml/badge.svg?branch=master"/></a>
<a href="https://github.com/Zen0x7/OpenDTE/actions/workflows/test.yml"><img src="https://github.com/Zen0x7/OpenDTE/actions/workflows/test.yml/badge.svg?branch=master"/></a>
<a href="https://codecov.io/gh/Zen0x7/OpenDTE"><img src="https://codecov.io/gh/Zen0x7/OpenDTE/graph/badge.svg?token=T564GYXC7Y"/></a>
</p>


## Introducción

Este proyecto ha sido creado con el propósito de proveer una solución de mayor nivel a la presentada por su [desarrollador inicial](https://github.com/gepd/HTTP-DTE). Debido a mi forma de ser, muy alejado de la participación, he decidido tomar su código base y respetando las licencias base.

## Licencia

Este proyecto está construido sobre [LibreDTE](https://github.com/LibreDTE/libredte-lib-core?tab=readme-ov-file#t%C3%A9rminos-y-condiciones-de-uso) y [HTTP-DTE](https://github.com/gepd/HTTP-DTE/blob/develop/LICENCE).

Por consecuencia el código añadido en este repositorio será liberado con su respectiva [licencia](/LICENSE).

## Objetivos del proyecto

- [ ] Sustituir la librería de LibreDTE con la última versión existente.
  - [ ] Estudiar la forma en la que opera LibreDTE (core).
- [ ] Realizar los preparativos para una versión preparada para producción.
  - [ ] Levantar un contenedor Docker y publicarlo en el registro público.
  - [ ] Configurar PHP para exponer un servicio de forma segura.
- [ ] Implementar medidas de seguridad de autenticación y protección de los endpoints.
- [ ] Implementar una interfaz gráfica que permite la revisión de las transacciones.