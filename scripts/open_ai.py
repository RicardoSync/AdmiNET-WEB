from openai import OpenAI

client = OpenAI(api_key="sk-proj-...")  # o usa .env si prefieres

response = client.chat.completions.create(
    model="gpt-4o",  # Puedes usar también "gpt-4" o "gpt-3.5-turbo"
    messages=[
        {"role": "user", "content": "Dime una historia corta de un unicornio"}
    ]
)

print(response.choices[0].message.content)
