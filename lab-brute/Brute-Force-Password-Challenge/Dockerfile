FROM python:3.10-slim

WORKDIR /app

COPY config/requirements.txt .
RUN pip install --no-cache-dir -r requirements.txt

COPY tools/ /app/tools/
COPY src/ /app/src/

EXPOSE 6001

CMD ["python", "src/app.py"]
