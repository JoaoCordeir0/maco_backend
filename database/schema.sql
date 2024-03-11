use maco;

create table user_lvl(
	id int auto_increment primary key,
    description varchar(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

create table user(
	id int auto_increment primary key,
    name varchar(255) not null,
    cpf varchar(30) not null unique, 
    email varchar(255) not null unique,
    password varchar(255) not null,
    lvl int not null,
    status bool not null,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    foreign key(lvl) REFERENCES user_lvl(id)    
);