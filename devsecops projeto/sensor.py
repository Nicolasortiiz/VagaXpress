import sqlite3
import random
import string
import time
from datetime import datetime

def estacionar(placa: str):
	conn = sqlite3.connect('estacionamento.db')
	cursor = conn.cursor()

	query_veiculo = "SELECT placa FROM VeiculoEstacionado WHERE placa = ?"
	cursor.execute(query_veiculo, (placa,))
	veiculo = cursor.fetchall()
	
	if not veiculo:
		query = "INSERT INTO VeiculoEstacionado (placa, dataEntrada, horaEntrada) VALUES (?, ?, ?)"
		cursor.execute(query, (placa, datetime.now().date(), datetime.now().time()))
		print(f"O veiculo com a placa {placa} entrou no estacionamento.")
		conn.commit()
	conn.close()
	time.sleep(random.randint(30, 60))
		
def sair_estacionamento(placa: str):
	conn = sqlite3.connect('estacionamento.db')
	cursor = conn.cursor()

	query_veiculo = "SELECT placa FROM VeiculoEstacionado WHERE placa = ?"
	cursor.execute(query_veiculo, (placa,))
	veiculo = cursor.fetchall()
	
	if veiculo:
		query = "UPDATE VeiculoEstacionado SET dataSaida = ?, horaSaida = ? WHERE placa = ?"
		cursor.execute(query, (datetime.now().date(), datetime.now().time(), placa))
		print(f"O veiculo com a placa {placa} saiu do estacionamento.")
		conn.commit()
		try:
			with open('placas.txt', 'a') as f:
				f.write(placa + '\n')
		except:
			print("Erro na escrita do arquivo.")
	conn.close()
	time.sleep(random.randint(30, 60))

def gerar_placa():
	letras = random.choices(string.ascii_uppercase, k=3) 
	numeros = random.choices(string.digits, k=4)  
	return ''.join(letras + numeros)

def retornar_placas() -> list:
    placas = []
    try:
        with open('placas.txt', 'r') as f:
            placas = [linha.strip() for linha in f.readlines()]
    except FileNotFoundError:
        print("Arquivo de placas não encontrado, iniciando com lista vazia.")
    return placas

if __name__ == "__main__":
	placas = retornar_placas()
	while True:
		x = random.randint(1, 2)
		if x == 1:
			placa = gerar_placa()
			placas.append(placa)
			estacionar(placa)
		if x == 2 and len(placas) > 0:
			sair_estacionamento(placas[random.randint(0, len(placas) - 1)])
				
		
	

	