import re
import mysql.connector
import paramiko
from datetime import datetime
import time

# === CONFIGURACIÓN ===
mikrotik_ip = "10.0.0.5"
usuario = "admin"
contrasena = "admin"
puerto_ssh = 22
interfaz = "ether23"
intervalo_segundos = 40  # 🕒 Cada cuánto capturar tráfi

db_config = {
    "host": "localhost",
    "user": "adminet",
    "password": "MinuzaFea265/",
    "database": "doblenet"
}
def capturar_y_guardar():
    try:
        # === SSH a MikroTik ===
        ssh = paramiko.SSHClient()
        ssh.set_missing_host_key_policy(paramiko.AutoAddPolicy())
        ssh.connect(mikrotik_ip, port=puerto_ssh, username=usuario, password=contrasena)
        stdin, stdout, stderr = ssh.exec_command(f"/interface/ethernet/print stats where name={interfaz}")
        salida = stdout.read().decode()
        ssh.close()

        # === Extraer valores ===
        rx_match = re.search(r"rx-bytes:\s*(\d+)", salida)
        tx_match = re.search(r"tx-bytes:\s*(\d+)", salida)

        rx_actual = int(rx_match.group(1)) if rx_match else 0
        tx_actual = int(tx_match.group(1)) if tx_match else 0

        # === Conexión a MySQL ===
        conn = mysql.connector.connect(**db_config)
        cursor = conn.cursor(dictionary=True)

        cursor.execute("SELECT rx, tx FROM consumo_internet ORDER BY fecha DESC LIMIT 1")
        fila = cursor.fetchone()

        rx_anterior = fila["rx"] if fila else 0
        tx_anterior = fila["tx"] if fila else 0

        rx_diff = max(0, rx_actual - rx_anterior)
        tx_diff = max(0, tx_actual - tx_anterior)
        total = rx_diff + tx_diff

        if rx_diff > 0 or tx_diff > 0:
            cursor.execute(
                "INSERT INTO consumo_internet (fecha, rx, tx, total) VALUES (NOW(), %s, %s, %s)",
                (rx_diff, tx_diff, total)
            )
            conn.commit()
            print(f"✅ [{datetime.now().strftime('%H:%M:%S')}] RX: {rx_diff} B | TX: {tx_diff} B")
        else:
            print(f"🔁 [{datetime.now().strftime('%H:%M:%S')}] Sin cambios detectados.")

        cursor.close()
        conn.close()

    except Exception as e:
        print("❌ Error:", str(e))

# === BUCLE INFINITO ===
print("🟢 Iniciando monitoreo de tráfico... (Ctrl+C para detener)")
while True:
    capturar_y_guardar()
    time.sleep(intervalo_segundos)
