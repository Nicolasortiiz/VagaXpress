import mariadb
try:
    conn = mariadb.connect(
        user="root", 
        password="senhaBD", 
        host="127.0.0.1", 
        port=3306, 
        database="estacionamentoDB"
    )
except:
    print("Erro ao conectar ao banco de dados!")