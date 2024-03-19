create database maco;

use maco;

create table role(
	id int auto_increment primary key,
    description varchar(255) not null,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

create table user(
	id int auto_increment primary key,
    name varchar(255) not null,
    cpf varchar(30) not null unique, 
    email varchar(255) not null unique,
    ra varchar(255) not null unique,
    password varchar(255) not null,
    role int not null,
    status bool not null,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    foreign key(role) REFERENCES role(id)    
);

create table course(
	id int auto_increment primary key,
    name varchar(255) not null unique,
    description text,     
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP    
);

create table user_course(
	id int auto_increment primary key,
    user int not null, 
    course int not null,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    foreign key(user) REFERENCES user(id),
    foreign key(course) REFERENCES course(id)    
);

create table article_status(
	id int auto_increment primary key,
    name varchar(255) not null,
    description text,     
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP   
);

create table article(
	id int auto_increment primary key,
    user int not null,
    title varchar(255) not null,
    authors varchar(999) not null,
    advisors varchar(999) not null,
    keywords varchar(255) not null,
    summary text not null,
    status int not null, 
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    foreign key(status) REFERENCES article_status(id),
    foreign key(user) REFERENCES user(id)
);