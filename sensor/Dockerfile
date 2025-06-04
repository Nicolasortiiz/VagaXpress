FROM localhost/python:latest

RUN apt-get update && apt-get install -y \
    gcc \
    default-libmysqlclient-dev 

WORKDIR /app

COPY requirements.txt .
COPY . .

RUN python -m venv venv
RUN . venv/bin/activate 
RUN pip install -r requirements.txt


EXPOSE 5001

CMD ["python", "main.py"]
