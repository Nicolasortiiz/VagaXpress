from flask import Flask
from flask_restful import Api
from simulador import SimularEntrada, SimularSaida, SimularAgendamentos

app = Flask(__name__)
api = Api(app)

# Rotas
api.add_resource(SimularEntrada, 
    '/simularEntrada/<string:placa>', 
    '/simularEntrada/<string:placa>/<int:horas>'
)

api.add_resource(SimularSaida, 
    '/simularSaida/<string:placa>'
)

api.add_resource(SimularAgendamentos, 
    '/simularAgendamentos'
)

if __name__ == '__main__':
    app.run(debug=False, port=5001)
