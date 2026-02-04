export async function fetchAddressByCep(cep) {
  if (!cep) return null;
  const cleaned = String(cep).replace(/[^0-9]/g, '');
  if (cleaned.length !== 8) return null;
  try {
    const res = await fetch(`https://viacep.com.br/ws/${cleaned}/json/`);
    if (!res.ok) return null;
    const data = await res.json();
    if (data.erro) return null;
    return {
      street: [data.logradouro, data.bairro].filter(Boolean).join(' - '),
      city: data.localidade || null,
      state: data.uf || null,
      zip_code: cleaned,
    };
  } catch (e) {
    return null;
  }
}
