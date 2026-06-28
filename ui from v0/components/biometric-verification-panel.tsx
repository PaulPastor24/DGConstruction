'use client'

import { makeStyles } from '@fluentui/react-components'

const useStyles = makeStyles({
  root: {
    backgroundColor: '#FFFFFF',
    borderRadius: '16px',
    padding: '32px',
    border: '1px solid rgba(0, 0, 0, 0.08)',
    boxShadow: '0 1px 3px rgba(0, 0, 0, 0.06)',
  },
  header: {
    marginBottom: '28px',
    paddingBottom: '20px',
    borderBottom: '1px solid rgba(0, 0, 0, 0.08)',
  },
  title: {
    fontSize: '20px',
    fontWeight: 700,
    color: '#2F6B3C',
    marginBottom: '6px',
  },
  subtitle: {
    fontSize: '14px',
    color: '#999999',
    fontWeight: 400,
  },
  content: {
    display: 'grid',
    gridTemplateColumns: 'repeat(4, 1fr)',
    gap: '24px',
    marginBottom: '28px',
    '@media (max-width: 1024px)': {
      gridTemplateColumns: 'repeat(2, 1fr)',
    },
    '@media (max-width: 600px)': {
      gridTemplateColumns: '1fr',
    },
  },
  card: {
    display: 'flex',
    flexDirection: 'column',
    gap: '12px',
    padding: '20px',
    backgroundColor: '#F5F5F0',
    borderRadius: '14px',
    border: '1px solid rgba(0, 0, 0, 0.06)',
  },
  icon: {
    fontSize: '32px',
    display: 'flex',
    alignItems: 'center',
    justifyContent: 'center',
  },
  label: {
    fontSize: '13px',
    color: '#999999',
    fontWeight: 600,
    textTransform: 'uppercase',
    letterSpacing: '0.5px',
  },
  value: {
    fontSize: '28px',
    fontWeight: 700,
    color: '#2F6B3C',
    lineHeight: '1.2',
  },
  description: {
    fontSize: '12px',
    color: '#666666',
    fontWeight: 400,
  },
  progressSection: {
    display: 'flex',
    flexDirection: 'column',
    gap: '12px',
    padding: '20px',
    backgroundColor: 'rgba(102, 187, 106, 0.08)',
    borderRadius: '14px',
    border: '1px solid rgba(102, 187, 106, 0.15)',
  },
  progressLabel: {
    display: 'flex',
    justifyContent: 'space-between',
    alignItems: 'center',
    marginBottom: '8px',
  },
  progressTitle: {
    fontSize: '14px',
    fontWeight: 600,
    color: '#333333',
  },
  progressPercentage: {
    fontSize: '16px',
    fontWeight: 700,
    color: '#2F6B3C',
  },
  progressBar: {
    height: '12px',
    backgroundColor: '#EEEEEA',
    borderRadius: '10px',
    overflow: 'hidden',
    boxShadow: 'inset 0 1px 2px rgba(0, 0, 0, 0.04)',
  },
  progressFill: {
    height: '100%',
    width: '100%',
    backgroundColor: '#66BB6A',
    transition: 'width 0.6s ease',
    borderRadius: '10px',
  },
  button: {
    padding: '12px 24px',
    backgroundColor: '#2F6B3C',
    color: '#FFFFFF',
    border: 'none',
    borderRadius: '10px',
    cursor: 'pointer',
    fontSize: '15px',
    fontWeight: 700,
    transition: 'all 0.3s ease',
    alignSelf: 'flex-start',
    '&:hover': {
      backgroundColor: '#1f4620',
      boxShadow: '0 4px 12px rgba(47, 107, 60, 0.3)',
      transform: 'translateY(-2px)',
    },
  },
})

export function BiometricVerificationPanel() {
  const styles = useStyles()

  const metrics = [
    { icon: '✓', label: 'Verified Scans', value: '48', description: '100% of present workers' },
    { icon: '⊘', label: 'Pending Verification', value: '0', description: 'Awaiting scan' },
    { icon: '❌', label: 'Failed Verification', value: '0', description: 'Retries needed' },
    { icon: '📊', label: 'Overall Success Rate', value: '100%', description: 'Biometric accuracy' },
  ]

  return (
    <div className={styles.root}>
      <div className={styles.header}>
        <div className={styles.title}>Biometric Verification Panel</div>
        <div className={styles.subtitle}>Track biometric fingerprint verification across the workforce.</div>
      </div>

      <div className={styles.content}>
        {metrics.map((metric, idx) => (
          <div key={idx} className={styles.card}>
            <div className={styles.icon}>{metric.icon}</div>
            <div className={styles.label}>{metric.label}</div>
            <div className={styles.value}>{metric.value}</div>
            <div className={styles.description}>{metric.description}</div>
          </div>
        ))}
      </div>

      <div className={styles.progressSection}>
        <div className={styles.progressLabel}>
          <span className={styles.progressTitle}>Biometric Scanning Progress</span>
          <span className={styles.progressPercentage}>100%</span>
        </div>
        <div className={styles.progressBar}>
          <div className={styles.progressFill} />
        </div>
        <div style={{ fontSize: '12px', color: '#666666', marginTop: '4px' }}>All 48 present workers verified</div>
      </div>

      <button className={styles.button} style={{ marginTop: '24px' }}>View Verification Logs</button>
    </div>
  )
}
