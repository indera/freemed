# $Id$
#
# Authors:
#      Jeff Buchbinder <jeff@freemedsoftware.org>
#
# FreeMED Electronic Medical Record and Practice Management System
# Copyright (C) 1999-2006 FreeMED Software Foundation
#
# This program is free software; you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation; either version 2 of the License, or
# (at your option) any later version.
#
# This program is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with this program; if not, write to the Free Software
# Foundation, Inc., 675 Mass Ave, Cambridge, MA 02139, USA.

SOURCE data/schema/mysql/patient.sql

CREATE TABLE IF NOT EXISTS `allergies` (
	allergy			VARCHAR (150) NOT NULL,
	severity		VARCHAR (150) NOT NULL,
	patient			INT UNSIGNED NOT NULL DEFAULT 0,
	reviewed		TIMESTAMP (14) NOT NULL DEFAULT NOW(),
	id			SERIAL,

	#	Define keys

	KEY			( patient, allergy ),
	FOREIGN KEY		( patient ) REFERENCES patient ( id ) ON DELETE CASCADE
) ENGINE=InnoDB;

#	Version 0.2.1
ALTER IGNORE TABLE allergies ADD COLUMN reviewed TIMESTAMP (14) NOT NULL DEFAULT NOW() AFTER patient;

