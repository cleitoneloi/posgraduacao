/*
  Verifica, no banco do TOTVS RM (SQL Server), qual coluna do módulo
  Processo Seletivo guarda o HTML de descrição do curso e qual o limite dela.
  Somente leitura (SELECT). Rodar em homologação ou com usuário de consulta.
*/

-- 1) Colunas de texto das tabelas do Processo Seletivo (prefixo SPS) e seu limite.
--    max_length = -1 significa (N)VARCHAR(MAX): até 2 GB, ou seja, sem limite prático.
SELECT  t.name                     AS tabela,
        c.name                     AS coluna,
        ty.name                    AS tipo,
        CASE WHEN c.max_length = -1 THEN 'MAX'
             WHEN ty.name IN ('nvarchar','nchar') THEN CAST(c.max_length / 2 AS varchar(10))
             ELSE CAST(c.max_length AS varchar(10)) END AS limite_caracteres
FROM    sys.tables  t
JOIN    sys.columns c  ON c.object_id = t.object_id
JOIN    sys.types   ty ON ty.user_type_id = c.user_type_id
WHERE   t.name LIKE 'SPS%'
  AND   ty.name IN ('varchar','nvarchar','text','ntext','char','nchar','varbinary','image')
  AND  (c.max_length = -1 OR c.max_length >= 1000 OR ty.name IN ('text','ntext','image'))
ORDER BY t.name, c.name;

-- 2) Localiza a coluna que já guarda o HTML de Endodontia (publicado hoje e ~15 KB)
--    e mostra o tamanho gravado, para comparar com os ~100 KB da versão base64.
DECLARE @sql nvarchar(max) = N'';
SELECT @sql = @sql + N'
SELECT ''' + t.name + N''' AS tabela, ''' + c.name + N''' AS coluna,
       DATALENGTH(' + QUOTENAME(c.name) + N') AS bytes_gravados
FROM ' + QUOTENAME(s.name) + N'.' + QUOTENAME(t.name) + N' WITH (NOLOCK)
WHERE CAST(' + QUOTENAME(c.name) + N' AS nvarchar(max)) LIKE N''%Endodontia%''
  AND CAST(' + QUOTENAME(c.name) + N' AS nvarchar(max)) LIKE N''%<div%'';'
FROM    sys.tables  t
JOIN    sys.schemas s  ON s.schema_id = t.schema_id
JOIN    sys.columns c  ON c.object_id = t.object_id
JOIN    sys.types   ty ON ty.user_type_id = c.user_type_id
WHERE   t.name LIKE 'SPS%'
  AND   ty.name IN ('varchar','nvarchar','text','ntext')
  AND  (c.max_length = -1 OR c.max_length >= 1000 OR ty.name IN ('text','ntext'));
EXEC sp_executesql @sql;
