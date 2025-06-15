from flask_restful import Resource
from flask import jsonify
import random
import mysql.connector
from dotenv import load_dotenv
from datetime import datetime, timedelta, time
import os


def get_connection():
    load_dotenv()
    db_user = os.getenv("DATABASE_USER")
    db_password = os.getenv("DATABASE_PASSWORD")
    return mysql.connector.connect(
        host='172.25.0.11',
        user=db_user,
        password=db_password,
        database='EstacionamentoDB',
        charset='utf8mb4',
        collation='utf8mb4_general_ci'
    )

def vagas_disponiveis(cursor):
    cursor.execute("SELECT totalVagas FROM Estacionamento LIMIT 1")
    total = cursor.fetchone()[0]
    cursor.execute("SELECT COUNT(*) FROM VagaOcupada")
    ocupadas = cursor.fetchone()[0]
    return total - ocupadas

class SimularEntrada(Resource):

    def get(self, placa, horas=None):
        conn = None
        cursor = None
        try:
            conn = get_connection()
            cursor = conn.cursor()

            if vagas_disponiveis(cursor) <= 0:
                return jsonify({"erro": "Estacionamento cheio"})
            select_registro = """
                SELECT idRegistro FROM Registro WHERE placa = %s AND horaEntrada IS NOT NULL AND horaSaida IS NULL ORDER BY idRegistro DESC LIMIT 1
            """
            cursor.execute(select_registro, (placa,))
            registro = cursor.fetchone()
            if registro:
                return jsonify({"erro": "Veículo já estacionado."})
            
            now = datetime.now()
            duracao = timedelta(hours=int(horas)) if horas else timedelta()   
            saida = now + duracao

            if duracao <= timedelta():
                insert_registro = """
                    INSERT INTO Registro (placa, dataEntrada, horaEntrada, statusPagamento)
                    VALUES (%s, %s, %s, %s)
                """
                values = (placa.upper(), now.date(), now.time(), False)
                cursor.execute(insert_registro, values)
                conn.commit()

                id_registro = cursor.lastrowid

                insert_ocupada = """
                    INSERT INTO VagaOcupada (idRegistro)
                    VALUES (%s)
                """
                cursor.execute(insert_ocupada, (id_registro,))
                conn.commit()
                return jsonify({"mensagem": "Entrada registrada", "placa": placa, "entrada": now})

            else:
                insert_registro = """
                    INSERT INTO Registro (placa, dataEntrada, dataSaida, horaEntrada, horaSaida, statusPagamento)
                    VALUES (%s, %s, %s, %s, %s, %s)
                """
                values = (placa.upper(), now.date(), saida.date(), now.time(), saida.time(), False)
                cursor.execute(insert_registro, values)
                conn.commit()

                return jsonify({"mensagem": "Entrada registrada", "placa": placa, "entrada": now, "saida": saida})
            

        except Exception as e:
            return jsonify({"erro": str(e)})
        finally:
            if cursor:
                cursor.close()
            if conn:
                conn.close()

class SimularSaida(Resource):

    def get(self, placa):
        conn = None
        cursor = None
        try:
            conn = get_connection()
            cursor = conn.cursor()
            select_registro = """
                SELECT idRegistro FROM Registro WHERE placa = %s AND horaSaida IS NULL ORDER BY idRegistro DESC LIMIT 1
            """
            cursor.execute(select_registro, (placa,))
            registro = cursor.fetchone()
            if not registro:
                return jsonify({"erro": "Veículo não estacionado."})

            id_registro = registro[0]
            now = datetime.now()
            cursor.execute("DELETE FROM VagaOcupada WHERE idRegistro = %s", (id_registro,))
            update_registro = """
                UPDATE Registro SET statusPagamento = FALSE, dataSaida = %s, horaSaida = %s WHERE idRegistro = %s
            """
            cursor.execute(update_registro, (now.date(), now.time(),id_registro,))
            conn.commit()

            return jsonify({"mensagem": f"Saída registrada para {placa}", "saida": now})

        except Exception as e:
            return jsonify({"erro": str(e)})
        finally:
            if cursor:
                cursor.close()
            if conn:
                conn.close()

class SimularAgendamentos(Resource):

    def get(self):
        conn = None
        cursor = None
        try:
            conn = get_connection()
            cursor = conn.cursor(dictionary=True)

            cursor.execute("SELECT * FROM VagaAgendada")
            agendados = cursor.fetchall()
            if not agendados:
                return jsonify({"mensagem": "Nenhum agendamento encontrado"})
            registros = []
            
            print("AGENDADOS:", agendados)
            print("PRIMEIRO REGISTRO:", agendados[0].keys())
            
            for ag in agendados:
                
                required_keys = ['placa', 'dataEntrada', 'horaEntrada']
                if not all(key in ag for key in required_keys):
                    continue

                total_seconds = int(ag['horaEntrada'].total_seconds())
                hours = total_seconds // 3600
                minutes = (total_seconds % 3600) // 60
                seconds = total_seconds % 60
                hora_entrada_time = time(hour=hours, minute=minutes, second=seconds)
                
                entrada = datetime.combine(ag['dataEntrada'], hora_entrada_time)
                print("HORA ENTRADA:", entrada)
                duracao = timedelta(hours=random.randint(1, 5), minutes=random.randint(0, 59))
                saida = entrada + duracao
                insert_registro = """
                    INSERT INTO Registro (placa, dataEntrada, dataSaida, horaEntrada, horaSaida, statusPagamento)
                    VALUES (%s, %s, %s, %s, %s, %s)
                """
                cursor.execute(insert_registro, (ag['placa'], ag['dataEntrada'], saida.date(), ag['horaEntrada'], saida.time(), False))
                conn.commit()

                delete_agendamento = """
                    DELETE FROM VagaAgendada WHERE idVagaAgendada = %s
                """
                cursor.execute(delete_agendamento, (ag['idVagaAgendada'],))
                conn.commit()

                registros.append({"placa": ag['placa'], "entrada": entrada, "saida": saida})

            return jsonify({"mensagem": "Agendamentos simulados", "registros": registros})

        except Exception as e:
            return jsonify({
                "erro": str(e),
                "tipo": type(e).__name__
            })
        finally:
            if cursor:
                cursor.close()
            if conn:
                conn.close()
