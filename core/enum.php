<?php 
	define('ENUM_CLASSES_DEFINED', true);
	define('_5MINS', 60*5);
	define('_3DAYS', (60*60*24*3));
	define('_DAY', (60*60*24));

	abstract class Status
	{
		const Error = 0;
		const Success = 1;
		const Warning = 2;
		const Unknow = 3;
	}
	abstract class SaveStatus
	{
		const None = 0;
		const Saving = 1;
		const Saved = 2;
		const Error = 3;
	}
	abstract class RegStatus
	{
		const Active = 0;
		const Banned = 1;
		const Deleted = 2;
	}
	abstract class PayStatus
	{
		const Pending = 0;
		const Paid = 1;
		const Deleted = 2;
	}
	abstract class FeedStatus
	{
		const Active = 0;
		const Banned = 1;
		const Deleted = 2;
	}

	abstract class RequestType
	{
		const Normal = 0;
		const Ajax = 1;
		const JPEG = 2;
		const JSON = 3;
	}

	abstract class Param
	{
		const Get = 0;
		const Post = 1;
		const Cookie = 2; 
		const Session = 3;
	}

	abstract class Sexo
	{
		const Indefinido = 0;
		const Femenino = 1;
		const Masculino = 2;
	}

	
	abstract class UserType
	{
		const Quest = 0;
		const User = 1;
		const Student = 2;
		const Teacher = 3;
		const Admin = 4;
	}

	/**
	 * Define el estado actual del usuario (info_user) los cuales pueden ser
	 * – Active:	Cuando el usuario esta actualmente activo (Aunque en plataforma no lo esté)
	 * – Break:		Cuando una persona solicita una baja temporal o deja de estar activo pero regresará
	 * – Drop:		Cuando el usuario se ha dado de baja definitiva o se va.
	 * – Graduated:	Solo para alumnos que han egresado
	 * – Applicant:	Cuando el registro solo es de algun aspirante
	 */
	abstract class UserStatus
	{
		const Active = 0;
		const Breaked = 1;
		const Drop = 2;
		const Graduated = 3;
		const Applicant = 4;
	}

	abstract class CategoryType
	{
		const Via = 0;
		const Campaign = 1;
		const Course = 2;
		const Institution = 3;
	}
	$_CATEGORIES = ['vias', 'courses', 'institution', 'campaigns'];

	// Para el log de Usuario
	abstract class Actions
	{
		const Unknow = 0;
		const View = 1;
		const Create = 2;
		const Update = 3;
		const Delete = 4;		
		const TryView = 11;
		const TryCreate = 12;
		const TryUpdate = 13;
		const TryDelete = 14;
	}
	abstract class Module
	{
		const General = 0;
		#	Applicants
		const ApplicantsList = 10;
		const Applicants = 11;
		const ApplicantsNotes = 12;
		const ApplicantsStats = 13;

		#	Categories
		const Categories = 20;

		#Messages
		const Messages = 30;
	}