import mysql.connector

conn = mysql.connector.connect(
	host='',
	user='root',
	password='root',
	database='EstacionamentoDB'
)

cursor = conn.cursor()

cursor.execute("INSERT INTO VeiculoEstacionado (placa,dataEntrada,horaEntrada) VALUES (%s,%,%)", (,,))

cursor.commit()

conn.close()