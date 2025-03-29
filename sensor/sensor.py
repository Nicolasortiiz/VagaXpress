import random
import string
import time
from datetime import datetime
from dbConnector import conn


def gerar_notafiscal(placa: str) -> bool:
	try:
		cursor = conn.cursor()

		query_estacionamento = "SELECT valorHora FROM Estacionamento LIMIT 1"
		cursor.execute(query_estacionamento)
		estacionamento = cursor.fetchone()
		valorHora = estacionamento[0]

		query_veiculo = "SELECT idVeiculoEstacionado, dataEntrada, horaEntrada FROM VeiculoEstacionado WHERE placa = ? AND dataSaida IS NULL AND horaSaida IS NULL"
		cursor.execute(query_veiculo, (placa,))
		veiculo = cursor.fetchone()

		dataEntrada = veiculo[1]
		horaEntrada = veiculo[2]
			
		dataSaida = datetime.now().date()
		horaSaida = datetime.now().time()

		entrada = datetime.combine(datetime.strptime(str(dataEntrada), '%Y-%m-%d'), datetime.strptime(str(horaEntrada), '%H:%M:%S').time())
		saida = datetime.combine(dataSaida, horaSaida)
		diferenca = saida - entrada

		totalHoras = diferenca.total_seconds() / 3600  
		if diferenca.seconds > 0:
			totalHoras = round(totalHoras) if totalHoras % 1 == 0 else int(totalHoras) + 1 

		valorTotal = totalHoras * valorHora
		query_nota_fiscal = "INSERT INTO NotaFiscal_Totem (dataEmissao, cpf, nome, valor) VALUES (?, ?, ?, ?)"
		cursor.execute(query_nota_fiscal, (dataSaida, gerar_cpf(), gerar_nome(), round(valorTotal, 2)))
		conn.commit()
		return True
	except Exception as e:
		print("Erro ao gerar nota fiscal:", e)
		return False


def gerar_nome():
    nomes = ["João", "Maria", "Carlos", "Ana", "Paulo", "Fernanda", "Lucas", "Beatriz", "Rafael", "Julia", "Ayrton", "Cleiton",
			"Luciano", "Leticia", "Bruno", "Cristina", "Ricardo", "Isabella", "Guilherme", "Camila", "Adenilson", "Gabriela",
			"Claurio", "Matheus", "Felipe", "Robinson", "Thiago", "Cristiane", "Vitor", "Luiza", "Davi", "Camila"]
    sobrenomes = ["Silva", "Oliveira", "Santos", "Pereira", "Costa", "Alves", "Rodrigues", "Gomes", "Lima", "Martins",
				"Carvalho", "Ferreira", "Mendes", "Ribeiro", "Almeida", "Nunes", "Santana", "Araujo", "Pinto", "Goncalves",
				"Barbosa", "Silveira", "Torres", "Pessoa", "Rocha", "Carvalho", "Moreira", "Araujo", "Lopes", "Cruz"]
    return random.choice(nomes) + " " + random.choice(sobrenomes)

def gerar_cpf():
    def digito(cpf):
        s = sum([int(cpf[i]) * (10 - i) for i in range(9)])
        return (11 - (s % 11)) % 10

    cpf = ''.join(random.choices(string.digits, k=9))
    return cpf + str(digito(cpf)) + str(digito(cpf + str(digito(cpf))))
def estacionar(placa: str):
	cursor = conn.cursor()

	query_veiculo = "SELECT placa FROM VeiculoEstacionado WHERE placa = ?"
	cursor.execute(query_veiculo, (placa,))
	veiculo = cursor.fetchall()
	
	if not veiculo:
		query = "INSERT INTO VeiculoEstacionado (placa, dataEntrada, horaEntrada) VALUES (?, ?, ?)"
		cursor.execute(query, (placa, datetime.now().date().isoformat(), datetime.now().time().isoformat()))
		print(f"O veiculo com a placa {placa} entrou no estacionamento.")
	
		id_veiculo = cursor.lastrowid
		query = "INSERT INTO Vagas (idVeiculoEstacionado) VALUES (?)"
		cursor.execute(query,(id_veiculo,))
		try:
			with open('placas.txt', 'a') as f:
				f.write(placa + '\n')
		except IOError as e:
			print(f"Erro ao registrar placa no arquivo: {e}")
		
		conn.commit()
	time.sleep(random.randint(30, 60))
		
def sair_estacionamento(placa: str):
	cursor = conn.cursor()

	query_veiculo = "SELECT idVeiculoEstacionado FROM VeiculoEstacionado WHERE placa = ? AND dataSaida IS NULL AND horaSaida IS NULL"
	cursor.execute(query_veiculo, (placa,))
	resultado = cursor.fetchone()
	
	if resultado and gerar_notafiscal(placa):
		query = "UPDATE VeiculoEstacionado SET dataSaida = ?, horaSaida = ? WHERE placa = ?"
		cursor.execute(query, (datetime.now().date().isoformat(), datetime.now().time().isoformat(), placa))
		print(f"O veiculo com a placa {placa} saiu do estacionamento.")

		id_veiculo = resultado[0]
		query = "DELETE FROM Vagas WHERE idVeiculoEstacionado = ?"
		cursor.execute(query, (id_veiculo,))
		conn.commit()

		try:
			with open('placas.txt', 'r') as f:
				linhas = f.readlines()
			linhas = [linha for linha in linhas if linha.strip() != placa]
			with open('placas.txt', 'w') as f:
				f.writelines(linhas)
		except:
			print("Erro na escrita do arquivo.")
			
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
	try:
		while True:
			x = random.randint(1, 2)
			if x == 1:
				placa = gerar_placa()
				placas.append(placa)
				estacionar(placa)
			if x == 2 and len(placas) > 0:
				placa = random.choice(placas)
				sair_estacionamento(placa)
				placas.remove(placa)			
	except KeyboardInterrupt:
		print("Encerrando o programa!")
	finally:
		conn.close() 
	

	