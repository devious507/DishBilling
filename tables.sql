DROP TABLE wincable;
DROP TABLE wincable_data;
DROP TABLE group_reports;
CREATE TABLE wincable_data (
	subnum varchar,
	subname varchar,
	address1 varchar,
	address2 varchar,
	address3 varchar,
	qty varchar,
	pkg_id varchar,
	pkg_name varchar,
	pkg_amt varchar,
	pkg_total varchar
);
CREATE TABLE group_reports (
	subname varchar,
	address1 varchar,
	category_name varchar,
	dates varchar,
	description varchar,
	quantity varchar,
	unit_price varchar,
	amount varchar);
