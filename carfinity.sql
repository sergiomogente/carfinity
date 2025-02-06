-- CREAR TABLA DE USUARIOS
CREATE TABLE Usuario (
    ID_Usuario INT AUTO_INCREMENT PRIMARY KEY,
    Nombre VARCHAR(100),
    Correo VARCHAR(100) UNIQUE,
    Contraseña VARCHAR(255),
    Tipo ENUM('admin', 'cliente') NOT NULL
);

-- CREAR TABLA DE COCHES
CREATE TABLE Coche (
    ID_Coche INT AUTO_INCREMENT PRIMARY KEY,
    Marca VARCHAR(50),
    Modelo VARCHAR(50),
    Año INT,
    Precio DECIMAL(10, 2),
    Descripcion TEXT,
    Estado ENUM('disponible', 'reservado', 'vendido') DEFAULT 'disponible',
    ID_Admin INT,
    FOREIGN KEY (ID_Admin) REFERENCES Usuario(ID_Usuario)
);

-- CREAR TABLA DE RESERVAS
CREATE TABLE Reserva (
    ID_Reserva INT AUTO_INCREMENT PRIMARY KEY,
    ID_Coche INT,
    ID_Cliente INT,
    Fecha_Reserva DATETIME DEFAULT CURRENT_TIMESTAMP,
    Estado ENUM('pendiente', 'confirmada', 'cancelada') DEFAULT 'pendiente',
    FOREIGN KEY (ID_Coche) REFERENCES Coche(ID_Coche),
    FOREIGN KEY (ID_Cliente) REFERENCES Usuario(ID_Usuario)
);

-- CREAR TABLA DE CHATS
CREATE TABLE Chat (
    ID_Chat INT AUTO_INCREMENT PRIMARY KEY,
    ID_Cliente INT NOT NULL,
    ID_Vendedor INT NOT NULL,
    Fecha_Creacion DATETIME DEFAULT CURRENT_TIMESTAMP,
    Estado ENUM('activo', 'cerrado') DEFAULT 'activo',
    FOREIGN KEY (ID_Cliente) REFERENCES Usuario(ID_Usuario),
    FOREIGN KEY (ID_Vendedor) REFERENCES Usuario(ID_Usuario)
);

-- CREAR TABLA DE MENSAJES
CREATE TABLE Mensaje (
    ID_Mensaje INT AUTO_INCREMENT PRIMARY KEY,
    ID_Chat INT NOT NULL,
    Remitente ENUM('cliente', 'vendedor') NOT NULL,
    Contenido TEXT NOT NULL,
    Fecha_Envio DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (ID_Chat) REFERENCES Chat(ID_Chat)
);

-- CREAR TABLA DE OPINIONES
CREATE TABLE Opinion (
    ID_Opinion INT AUTO_INCREMENT PRIMARY KEY,
    ID_Coche INT NOT NULL,
    ID_Cliente INT NOT NULL,
    Comentario TEXT,
    Valoracion INT CHECK(Valoracion BETWEEN 1 AND 5),
    Fecha DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (ID_Coche) REFERENCES Coche(ID_Coche),
    FOREIGN KEY (ID_Cliente) REFERENCES Usuario(ID_Usuario)
);

-- CREAR TABLA DE SERVICIOS ADICIONALES
CREATE TABLE Servicio_Adicional (
    ID_Servicio INT AUTO_INCREMENT PRIMARY KEY,
    Nombre VARCHAR(100),
    Descripcion TEXT,
    Precio DECIMAL(10, 2)
);

-- CREAR TABLA INTERMEDIA ENTRE CLIENTES Y SERVICIOS ADICIONALES
CREATE TABLE Cliente_Servicio (
    ID_Cliente INT,
    ID_Servicio INT,
    Fecha_Contratacion DATETIME DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (ID_Cliente, ID_Servicio),
    FOREIGN KEY (ID_Cliente) REFERENCES Usuario(ID_Usuario),
    FOREIGN KEY (ID_Servicio) REFERENCES Servicio_Adicional(ID_Servicio)
);
