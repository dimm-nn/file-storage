Docker
===========
1. [Install Docker](https://docs.docker.com/engine/installation/linux/ubuntulinux/#/install)
2. `sudo usermod -aG docker $USER`, re-login
3. [Install docker-compose](https://github.com/docker/compose/releases/latest)

Deploy app
===========
1. Clone repository
`git clone git@github.com:consultnn/file-storage.git`
2. Move in project dir
`cd file-storage`
3. Run docker containers
`docker-compose up -d`
4. Set build scripts executable
`chmod +x ./build/*`
6. Init DEV environment
`./build/dev.sh`

Build scripts (placed in directory `./build`)
===========
 - prod.sh, dev.sh, test.sh - init environment
 
Other documentation
===========
 - [API](docks/api.md)
 - [Configuration](docks/configuration.md)
